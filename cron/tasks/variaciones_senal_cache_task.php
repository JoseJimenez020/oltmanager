<?php
/**
 * cron/tasks/variaciones_senal_cache_task.php
 * DEPLOY: C:\xampp\htdocs\oltmanager\cron\tasks\variaciones_senal_cache_task.php
 *
 * v2: corrige un bug de atomicidad. TRUNCATE TABLE en MySQL/InnoDB hace un
 * COMMIT implícito -- aunque estuviera "dentro" de beginTransaction()/commit(),
 * la tabla quedaba vacía y visible para otras conexiones (la página web)
 * ANTES de que terminaran de insertarse las filas nuevas. Eso causaba que
 * una carga de página, en la ventana entre el TRUNCATE y el INSERT, viera
 * "Sin variaciones de señal" aunque sí había datos. Ahora se usa DELETE
 * FROM (sí es transaccional): otras conexiones ven la tabla vieja completa
 * hasta que el commit() se confirma, nunca un estado a medias.
 *
 * También se agrega un lock de archivo (mismo patrón que run_all.php) para
 * que si esta tarea llegara a tardar más de una hora, la siguiente
 * ejecución del Task Scheduler no se solape con la anterior.
 *
 */

require_once(__DIR__ . '/../../db/conn.php');

const VSC_VENTANA_HORAS            = 6;
const VSC_UMBRAL_EVENTO_DB         = 1.0;
const VSC_UMBRAL_CRITICO_RX        = -32.0;
const VSC_UMBRAL_VAR_CRITICO_DB    = 5.0;
const VSC_UMBRAL_EVENTOS_INESTABLE = 3;

date_default_timezone_set('America/Merida');
set_time_limit(300);

// ---- Lock: evita solapamiento si una corrida se alarga más de una hora ----
$lockFile = __DIR__ . '/variaciones_senal_cache.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Ejecución previa aún en curso, se omite este ciclo.\n");
    exit(0);
}

function vsc_clasificarSeveridad(float $rxActual, float $var, int $eventos): string
{
    $varAbs = abs($var);
    if ($rxActual <= VSC_UMBRAL_CRITICO_RX || $varAbs >= VSC_UMBRAL_VAR_CRITICO_DB) {
        return 'critico';
    }
    if ($eventos >= VSC_UMBRAL_EVENTOS_INESTABLE) {
        return 'inestable';
    }
    return 'advertencia';
}

try {
    $pdo = (new DbConn())->getPdo();
    $umbralEventoRaw = (int) (VSC_UMBRAL_EVENTO_DB * 1000);

    $sql = "WITH lecturas AS (
            SELECT hp.IdOnu, hp.RxOnu, hp.HFecha,
                   LAG(hp.RxOnu) OVER (PARTITION BY hp.IdOnu ORDER BY hp.HFecha) AS RxAnterior
            FROM historial_potencia hp
            WHERE hp.HFecha >= (NOW() - INTERVAL :ventanaHoras HOUR)
        ),
        eventos_por_onu AS (
            SELECT IdOnu, COUNT(*) AS TotalEventos
            FROM lecturas
            WHERE RxAnterior IS NOT NULL
              AND ABS(RxOnu - RxAnterior) >= :umbralEvento
            GROUP BY IdOnu
        ),
        extremos AS (
            SELECT IdOnu, MIN(HFecha) AS PrimeraFecha, MAX(HFecha) AS UltimaFecha
            FROM historial_potencia
            WHERE HFecha >= (NOW() - INTERVAL :ventanaHoras2 HOUR)
            GROUP BY IdOnu
        )
        SELECT
            o.OntId, o.OnuSn,
            g.IndexCard, g.IndexPort,
            ol.OltIdApi, ol.OltName,
            hp_prev.RxOnu AS RxPrevio,
            hp_curr.RxOnu AS RxActual,
            COALESCE(e.TotalEventos, 0) AS Eventos,
            ext.UltimaFecha
        FROM extremos ext
        INNER JOIN onu o          ON o.OntId = ext.IdOnu
        INNER JOIN gpon g         ON g.IdOlt = o.OntGpon
        INNER JOIN olts_list ol   ON ol.OltIdApi = o.OntOlt
        INNER JOIN historial_potencia hp_prev
            ON hp_prev.IdOnu = ext.IdOnu AND hp_prev.HFecha = ext.PrimeraFecha
        INNER JOIN historial_potencia hp_curr
            ON hp_curr.IdOnu = ext.IdOnu AND hp_curr.HFecha = ext.UltimaFecha
        LEFT JOIN eventos_por_onu e ON e.IdOnu = ext.IdOnu
        WHERE COALESCE(e.TotalEventos, 0) >= 1";

    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':ventanaHoras', VSC_VENTANA_HORAS, PDO::PARAM_INT);
    $stmt->bindValue(':ventanaHoras2', VSC_VENTANA_HORAS, PDO::PARAM_INT);
    $stmt->bindValue(':umbralEvento', $umbralEventoRaw, PDO::PARAM_INT);
    $stmt->execute();
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $fechaCalculo = date('Y-m-d H:i:s');
    $filasCache = [];

    foreach ($filas as $f) {
        $previo    = ((int) $f['RxPrevio']) / 1000;
        $actual    = ((int) $f['RxActual']) / 1000;
        $var       = round($actual - $previo, 1);
        $eventos   = (int) $f['Eventos'];
        $severidad = vsc_clasificarSeveridad($actual, $var, $eventos);

        $filasCache[] = [
            (int) $f['OntId'],
            $f['OnuSn'],
            (int) $f['OltIdApi'],
            $f['OltName'],
            (int) $f['IndexCard'],
            (int) $f['IndexPort'],
            (int) $f['RxPrevio'],
            (int) $f['RxActual'],
            $var,
            $eventos,
            $severidad,
            $f['UltimaFecha'],
            $fechaCalculo,
        ];
    }

    $pdo->beginTransaction();

    // CAMBIO CLAVE: DELETE en vez de TRUNCATE. DELETE sí respeta la
    // transacción -- otras conexiones (la página web) siguen viendo la
    // tabla anterior COMPLETA hasta que este commit() se confirme, nunca
    // un estado vacío a medias.
    $pdo->exec('DELETE FROM variaciones_senal_cache');

    if ($filasCache) {
        $chunkSize = 500;
        foreach (array_chunk($filasCache, $chunkSize) as $chunk) {
            $placeholders = implode(',', array_fill(0, count($chunk), '(?,?,?,?,?,?,?,?,?,?,?,?,?)'));
            $sqlInsert = "INSERT INTO variaciones_senal_cache
                (OntId, OnuSn, OltIdApi, OltName, IndexCard, IndexPort,
                 RxPrevio, RxActual, VarDb, Eventos, Severidad, UltimaFecha, LastUpdated)
                VALUES $placeholders";

            $params = [];
            foreach ($chunk as $fila) {
                foreach ($fila as $valor) {
                    $params[] = $valor;
                }
            }
            $pdo->prepare($sqlInsert)->execute($params);
            set_time_limit(120);
        }
    }

    $pdo->commit();

    $resumen = count($filasCache) . " ONUs con variacion de señal cacheadas (ventana " . VSC_VENTANA_HORAS . "h)";
    echo "[" . date('Y-m-d H:i:s') . "] $resumen\n";
} catch (\Throwable $e) {
    if (isset($pdo) && $pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log('[variaciones_senal_cache_task] Error: ' . $e->getMessage());
    echo "[" . date('Y-m-d H:i:s') . "] Error: " . $e->getMessage() . "\n";
} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}