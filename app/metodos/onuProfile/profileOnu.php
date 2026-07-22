<?php
require_once(__DIR__ . '/../snmp/oltSnmp.php');
require_once(__DIR__ . '/../snmp/profileOnu.php');
require_once(__DIR__ . '/onuProfileController.php');
require_once(__DIR__ . '/../speedProfile/speedProfileController.php');
require_once(__DIR__ . '/../oltProfile/oltProfileController.php');
require_once(__DIR__ . '/../vlanProfile/vlanProfileController.php');
require_once(__DIR__ . '/../potencia/potenciaController.php');

class profileOnu
{
    private $onu;
    private $speed;
    public $olt;
    private $vlan;
    private $potencia;
    private $pdo; // NUEVO
    private $rxHistoryStmt; // NUEVO, prepared statement reutilizado
    public static $ol;
    public static $gpon;

    public function __construct()
    {
        $this->onu = new onuProfileController();
        $this->speed = new speedProfileController();
        $this->olt = new oltProfileController();
        $this->vlan = new vlanProfileController();
        $this->potencia = new potenciaController();
        $this->pdo = (new DbConn())->getPdo(); // NUEVO
    }

    private function insertRxHistory(int $ontId, $rx, string $date): void
    {
        if ($this->rxHistoryStmt === null) {
            $this->rxHistoryStmt = $this->pdo->prepare(
                'INSERT INTO historial_potencia (IdOnu, RxOnu, HFecha) VALUES (?, ?, ?)'
            );
        }
        try {
            $this->rxHistoryStmt->execute([$ontId, $rx, $date]);
        } catch (\Throwable $e) {
            error_log("[profileOnu::insertRxHistory] Fallo OntId $ontId: " . $e->getMessage());
        }
    }

    public function insertOnus($ont)
    {
        if (empty($ont))
            return false;
        return $this->onu->insertOnus($ont);
    }

    public function insertPotencia($ont)
    {
        if (empty($ont))
            return false;
        return $this->potencia->insertPotencia(self::$gpon, $ont);
    }

    public function insertVlans($vlans)
    {
        if (empty($vlans))
            return false;
        return $this->vlan->insertVlanOnu($vlans);
    }

    public function setOlt()
    {
        self::$ol = $this->olt->getOlt();
    }

    public function setGponOnu()
    {
        self::$gpon = $this->onu->getOnuGponInfoVlan();
    }

    public function unsetGponOnu()
    {
        self::$gpon = null;
    }

    public static function getGpon(): ?array
    {
        if (empty(self::$gpon))
            return null;
        return self::$gpon;
    }

    public function getVlans()
    {
        $vlans = [];
        foreach (self::$ol as $v) {
            $snmp = new nSnmp($v['OltIdApi'], 'read');
            $s = new profileOnuS($snmp);
            $vlanS = $s->getWalk('VLAN');
            $s->close();
            if (empty($vlanS))
                continue;
            $vl = $this->vlan->getVlansOnus(self::$gpon, $vlanS, $v['OltIdApi']);
            $vlans = array_merge($vlans, $vl);
        }
        return $vlans;
    }

    public function getVlan($zona)
    {
        $snmp = new nSnmp($zona, 'read');
        $s = new profileOnuS($snmp);
        $vlanS = $s->getWalk('VLAN');
        $s->close();
        if (empty($vlanS))
            return null;
        return $this->vlan->getVlansOnus(self::$gpon, $vlanS, $zona);
    }

    public function newVlans($update, $vlans)
    {
        $coincidencias = [];
        foreach ($update['nuevo'] as $upd) {
            $index = $upd['index'];
            $olt = $upd['olt'];
            foreach ($vlans as $vlan) {
                if ($vlan['Index'] === $index && $vlan['Olt'] === $olt) {
                    $coincidencias[] = $vlan;
                }
            }
        }
        return $coincidencias;
    }

    public function getProfiles(): array
    {
        $ont = [];
        foreach (self::$ol as $v) {
            $inicio = microtime(true);
            $p = $this->getProfile($v['OltIdApi']);
            $elapsed = round(microtime(true) - $inicio, 2);
            error_log("[profileOnu::getProfiles] OLT {$v['OltName']} tardó {$elapsed}s");
            if (is_null($p))
                continue;
            $ont = array_merge($ont, $p);
            set_time_limit(900);
        }
        return $ont;
    }

    public function getProfile($zona): ?array
    {
        $snmp = new nSnmp($zona, 'read');
        $s = new profileOnuS($snmp);
        $g = $this->onu->getCard();
        $up = $this->speed->getSpeed('up', $zona);
        $down = $this->speed->getSpeed('down', $zona);
        $r = $s->getProfile();
        $s->close();

        if (is_null($r))
            return null;

        $n = [];
        for ($i = 0; $i < count($r); $i++) {
            $indexOid = $r[$i]['index'];
            $tcontIndex = $r[$i]['tcont'];
            $gemportIndex = $r[$i]['gemport'];

            // El index SNMP no existe en la tabla gpon: la ONU no está registrada
            // en la DB todavía. Se omite para no generar el Warning.
            if (!isset($g[$indexOid])) {
                continue;
            }

            // El speed profile no existe en la DB para esta zona todavía.
            if (!isset($up[$tcontIndex]) || !isset($down[$gemportIndex])) {
                continue;
            }

            $n[] = [
                'index' => $indexOid . '.' . $r[$i]['pos'],
                'gpon' => $g[$indexOid][0],
                'pos' => $r[$i]['pos'],
                'name' => $r[$i]['name'],
                'model' => $r[$i]['model'],
                'desc' => $r[$i]['desc'],
                'dis' => $r[$i]['dis'],
                'rx' => $r[$i]['rx'],
                'status' => $r[$i]['status'],
                'sn' => $r[$i]['sn'],
                'tcont' => $up[$tcontIndex][0],
                'gemport' => $down[$gemportIndex][0],
                'olt' => $zona,
            ];
        }
        return $n;
    }

    public function formatOnu($onu)
    {
        $sn = self::$gpon;
        $resultado = ['existe' => [], 'migracion' => [], 'nuevo' => []];
        date_default_timezone_set('America/Merida');
        $fecha = date('Y-m-d H:i:s'); // NUEVO: una sola fecha para todo el ciclo

        foreach ($onu as $n) {
            $serial = strtoupper(trim($n['sn']));   // normaliza
            $index = null;
            foreach ($sn as $dbSerial => $dbData) {
                if (strtoupper(trim($dbSerial)) === $serial) {
                    $index = $dbData;
                    break;
                }
            }
            if (isset($index)) {
                $indexViejo = $index['IndexOid'] . '.' . $index['OntPos'];

                // NUEVO: registrar histórico de RX, el equipo ya existe en DB
                // sin importar si coincide el puerto (existe) o migró (migracion)
                $this->insertRxHistory((int) $index['OntId'], $n['rx'], $fecha);

                if ($n['index'] === $indexViejo) {
                    $resultado['existe'][] = $n;
                } else {
                    $n['indexViejo'] = $indexViejo;
                    $resultado['migracion'][] = $n;
                }
            } else {
                $resultado['nuevo'][] = $n;
            }
        }
        return $resultado;
    }
}
