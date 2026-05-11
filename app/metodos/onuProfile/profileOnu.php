<?php
require_once(__DIR__ . '/../../snmp/oltSnmp.php');
require_once(__DIR__ . '/../../snmp/profileOnu.php');
require_once(__DIR__ . '/onuProfileController.php');
require_once(__DIR__ . '/../../speedProfile/speedProfileController.php');
require_once(__DIR__ . '/../../oltProfile/oltProfileController.php');
require_once(__DIR__ . '/../../vlanProfile/vlanProfileController.php');
require_once(__DIR__ . '/../../potencia/potenciaController.php');

class profileOnu
{
    private $onu;
    private $speed;
    public  $olt;
    private $vlan;
    private $potencia;
    public static $ol;
    public static $gpon;

    public function __construct()
    {
        $this->onu      = new onuProfileController();
        $this->speed    = new speedProfileController();
        $this->olt      = new oltProfileController();
        $this->vlan     = new vlanProfileController();
        $this->potencia = new potenciaController();
    }

    public function insertOnus($ont)
    {
        if (empty($ont)) return false;
        return $this->onu->insertOnus($ont);
    }

    public function insertPotencia($ont)
    {
        if (empty($ont)) return false;
        return $this->potencia->insertPotencia(self::$gpon, $ont);
    }

    public function insertVlans($vlans)
    {
        if (empty($vlans)) return false;
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
        if (empty(self::$gpon)) return null;
        return self::$gpon;
    }

    public function getVlans()
    {
        $vlans = [];
        foreach (self::$ol as $v) {
            $snmp  = new nSnmp($v['OltIdApi'], 'read');
            $s     = new profileOnuS($snmp);
            $vlanS = $s->getWalk('VLAN');
            $s->close();
            if (empty($vlanS)) continue;
            $vl    = $this->vlan->getVlansOnus(self::$gpon, $vlanS, $v['OltIdApi']);
            $vlans = array_merge($vlans, $vl);
        }
        return $vlans;
    }

    public function getVlan($zona)
    {
        $snmp  = new nSnmp($zona, 'read');
        $s     = new profileOnuS($snmp);
        $vlanS = $s->getWalk('VLAN');
        $s->close();
        if (empty($vlanS)) return null;
        return $this->vlan->getVlansOnus(self::$gpon, $vlanS, $zona);
    }

    public function newVlans($update, $vlans)
    {
        $coincidencias = [];
        foreach ($update['nuevo'] as $upd) {
            $index = $upd['index'];
            $olt   = $upd['olt'];
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
            $p = $this->getProfile($v['OltIdApi']);
            if (is_null($p)) continue;
            $ont = array_merge($ont, $p);
            set_time_limit(120);
        }
        return $ont;
    }

    public function getProfile($zona): ?array
    {
        $snmp = new nSnmp($zona, 'read');
        $s    = new profileOnuS($snmp);
        $g    = $this->onu->getCard();
        $up   = $this->speed->getSpeed('up', $zona);
        $down = $this->speed->getSpeed('down', $zona);
        $r    = $s->getProfile();
        $s->close();

        if (is_null($r)) return null;

        $n = [];
        for ($i = 0; $i < count($r); $i++) {
            $n[] = [
                'index'   => $r[$i]['index'] . '.' . $r[$i]['pos'],
                'gpon'    => $g[$r[$i]['index']][0],
                'pos'     => $r[$i]['pos'],
                'name'    => $r[$i]['name'],
                'model'   => $r[$i]['model'],
                'desc'    => $r[$i]['desc'],
                'dis'     => $r[$i]['dis'],
                'rx'      => $r[$i]['rx'],
                'status'  => $r[$i]['status'],
                'sn'      => $r[$i]['sn'],
                'tcont'   => $up[$r[$i]['tcont']][0],
                'gemport' => $down[$r[$i]['gemport']][0],
                'olt'     => $zona,
            ];
        }
        return $n;
    }

    public function formatOnu($onu)
    {
        $sn        = self::$gpon;
        $resultado = ['existe' => [], 'migracion' => [], 'nuevo' => []];

        foreach ($onu as $n) {
            $serial    = $n['sn'];
            $indexNuevo = $n['index'];
            $index     = @$sn[$serial];

            if (isset($index)) {
                $indexViejo = $index['IndexOid'] . '.' . $index['OntPos'];
                if ($indexNuevo === $indexViejo) {
                    $resultado['existe'][] = $n;
                } else {
                    $resultado['migracion'][] = $n;
                    $lastIndex = array_key_last($resultado['migracion']);
                    $resultado['migracion'][$lastIndex]['indexViejo'] = $indexViejo;
                }
            } else {
                $resultado['nuevo'][] = $n;
            }
        }
        return $resultado;
    }
}
