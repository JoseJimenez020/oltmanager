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

    private $pendingRxHistory = []; // NUEVO: acumula [OntId, Rx, Fecha] en memoria

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
        $this->pendingRxHistory[] = [$ontId, $rx, $date];
    }

    /**
     * Escribe todo el histórico acumulado durante formatOnu() en lotes,
     * en vez de un INSERT por ONU. Con miles de ONUs y latencia de red hacia
     * la DB remota, un INSERT individual por fila puede sumar minutos; en
     * lotes de 500 filas, el mismo trabajo toma segundos.
     */
    private function flushRxHistory(): void
    {
        if (empty($this->pendingRxHistory))
            return;

        $chunkSize = 500;
        $chunks = array_chunk($this->pendingRxHistory, $chunkSize);

        foreach ($chunks as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '(?,?,?)'));
            $sql = "INSERT INTO historial_potencia (IdOnu, RxOnu, HFecha) VALUES $placeholders";
            $params = [];
            foreach ($chunk as $row) {
                $params[] = $row[0];
                $params[] = $row[1];
                $params[] = $row[2];
            }
            try {
                $this->pdo->prepare($sql)->execute($params);
            } catch (\Throwable $e) {
                error_log("[profileOnu::flushRxHistory] Fallo en lote: " . $e->getMessage());
            }
        }

        $this->pendingRxHistory = []; // liberar memoria tras escribir
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
            $inicio = microtime(true);
            $snmp = new nSnmp($v['OltIdApi'], 'read');
            $s = new profileOnuS($snmp);
            $vlanS = $s->getWalk('VLAN');
            $s->close();
            $elapsed = round(microtime(true) - $inicio, 2);
            error_log("[profileOnu::getVlans] OLT {$v['OltName']} tardo {$elapsed}s");
            if (empty($vlanS))
                continue;
            $vl = $this->vlan->getVlansOnus(self::$gpon, $vlanS, $v['OltIdApi']);
            $vlans = array_merge($vlans, $vl);
            set_time_limit(300);
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
        // Margen generoso sobre el peor caso normal observado (~47s para
        // deliciasChihuahua en condiciones sanas). 90s da espacio de
        // sobra sin dejar que una OLT degradada arrastre el ciclo entero
        // como pasó el 2026-08-04 (152.75s, 137.61s, 96.91s en la misma
        // corrida).
        $timeoutPorOlt = 90;

        foreach (self::$ol as $v) {
            $inicio = microtime(true);
            $p = $this->getProfileAislado($v['OltIdApi'], $timeoutPorOlt);
            $elapsed = round(microtime(true) - $inicio, 2);
            $etiqueta = is_null($p) ? ' (FALLO/TIMEOUT, se omite este ciclo)' : '';
            error_log("[profileOnu::getProfiles] OLT {$v['OltName']} tardó {$elapsed}s{$etiqueta}");
            if (is_null($p))
                continue;
            $ont = array_merge($ont, $p);
            set_time_limit(300);
        }
        return $ont;
    }

    /**
     * Corre getProfile($oltIdApi) en un SUBPROCESO PHP independiente con
     * límite de tiempo forzado por el sistema operativo. Si el hijo no
     * termina a tiempo, se mata con proc_terminate() (equivalente a
     * TerminateProcess en Windows) y esta OLT se omite en este ciclo,
     * sin arrastrar al resto del cron. Se reintenta automáticamente en
     * el siguiente ciclo (5-10 min después), no requiere intervención.
     *
     * Por qué un subproceso y no solo set_time_limit(): PHP no puede
     * interrumpir de forma confiable una llamada SNMP bloqueada en
     * Windows (el motor solo revisa el límite entre instrucciones, y una
     * llamada atascada dentro de la extensión C nunca le da esa
     * oportunidad). Un proceso hijo sí puede matarse desde afuera sin
     * depender de que coopere.
     */
    private function getProfileAislado(string $oltIdApi, int $timeoutSegundos): ?array
    {
        $phpBinary = PHP_BINARY;
        $script = __DIR__ . '/../../../cron/tasks/fetch_olt_profile.php';
        $cmd = [$phpBinary, $script, (string) $oltIdApi];

        // Archivos temporales para stdout/stderr del hijo. Nombre único
        // por llamada (tempnam) para que corridas/OLTs concurrentes o
        // consecutivas nunca se pisen entre sí.
        $archivoSalida = tempnam(sys_get_temp_dir(), 'olt_profile_out_');
        $archivoErrores = tempnam(sys_get_temp_dir(), 'olt_profile_err_');

        $descriptorspec = [
            0 => ['pipe', 'r'],
            1 => ['file', $archivoSalida, 'w'],
            2 => ['file', $archivoErrores, 'w'],
        ];

        $opciones = ['bypass_shell' => true];

        $process = proc_open($cmd, $descriptorspec, $pipes, null, null, $opciones);
        if (!is_resource($process)) {
            error_log("[profileOnu::getProfileAislado] No se pudo lanzar subproceso para OLT $oltIdApi");
            @unlink($archivoSalida);
            @unlink($archivoErrores);
            return null;
        }

        // No se necesita enviar nada al hijo por stdin.
        fclose($pipes[0]);

        $inicioEspera = time();
        $matadoPorTimeout = false;

        // Polling simple: SOLO se consulta el estado del proceso, sin
        // tocar ningún pipe. Esto es lo que sí funciona de forma
        // confiable en Windows.
        while (true) {
            $estado = proc_get_status($process);

            if (!$estado['running']) {
                break;
            }

            if ((time() - $inicioEspera) >= $timeoutSegundos) {
                proc_terminate($process, 9);
                $matadoPorTimeout = true;
                error_log("[profileOnu::getProfileAislado] OLT $oltIdApi excedió {$timeoutSegundos}s, proceso terminado forzosamente");
                break;
            }

            usleep(200000); // 200ms entre revisiones
        }

        proc_close($process);

        // Pequeño margen para que Windows termine de soltar el archivo
        // tras matar el proceso, antes de intentar leerlo.
        if ($matadoPorTimeout) {
            usleep(100000);
        }

        $salida = @file_get_contents($archivoSalida);
        $errores = @file_get_contents($archivoErrores);

        @unlink($archivoSalida);
        @unlink($archivoErrores);

        if ($matadoPorTimeout) {
            return null;
        }

        if (!empty($errores)) {
            error_log("[profileOnu::getProfileAislado] stderr de OLT $oltIdApi: " . trim($errores));
        }

        $decoded = json_decode(trim((string) $salida), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("[profileOnu::getProfileAislado] Respuesta inválida del subproceso para OLT $oltIdApi: " . substr((string) $salida, 0, 200));
            return null;
        }
        return $decoded;
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
        $fecha = date('Y-m-d H:i:s');

        // OPTIMIZACION CRITICA: antes, por cada ONU leída por SNMP (N) se
        // recorría TODO el mapa de ONUs en DB (M) comparando seriales
        // normalizados uno por uno => O(N x M). Con miles de ONUs en ambos
        // lados esto son millones de comparaciones de string y explicaba
        // ~380s de los ~983s totales del ciclo (medido en producción el
        // 2026-07-31). Ahora se normaliza el mapa UNA sola vez -> O(M), y
        // cada búsqueda posterior es O(1) por hash en vez de escaneo lineal.
        $snNormalizado = [];
        foreach ($sn as $dbSerial => $dbData) {
            $snNormalizado[strtoupper(trim($dbSerial))] = $dbData;
        }

        foreach ($onu as $n) {
            $serial = strtoupper(trim($n['sn']));
            $index = $snNormalizado[$serial] ?? null;         // <-- O(1), sin loop interno

            if (isset($index)) {
                $indexViejo = $index['IndexOid'] . '.' . $index['OntPos'];
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

        $this->flushRxHistory();
        return $resultado;
    }
}
