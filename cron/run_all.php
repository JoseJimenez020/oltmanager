<?php
/**
 * cron/run_all.php
 *
 * Orquestador único para las tareas periódicas de OLT Manager.
 * Reemplaza los dos .bat (refresh_cron.bat y el de refreshDb.php) para que
 * NUNCA corran en paralelo y compitan por sesiones SNMP contra las mismas OLTs.
 *
 * Orden de ejecución (secuencial, nunca paralelo):
 *   1. refresh_db            -> lee SNMP de todas las OLTs, actualiza status/potencia,
 *                                inserta vlans nuevas, migra ONUs, banda ancha.
 *                                Ya escribe historial_potencia dentro de profileOnu::formatOnu().
 *   2. unconfigured_cache    -> cuenta ONUs desautorizadas por OLT (para el dashboard).
 *   3. purge_historial       -> elimina filas de historial_potencia mayores a N días.
 *
 * Este script se ejecuta por CRON del sistema (Task Scheduler de Windows),
 * NUNCA por una petición web.
 *
 * INSTALACION (reemplaza ambas tareas anteriores por esta única):
 *   schtasks /create /tn "OltManager RunAll" /tr "C:\xampp\htdocs\oltmanager\cron\run_all.bat" /sc minute /mo 6 /f
 */

set_time_limit(0);
date_default_timezone_set('America/Merida');

// ---- Lock global: evita que una corrida se solape con la anterior si tarda
// más de los 6 minutos entre disparos del Task Scheduler ----
$lockFile = __DIR__ . '/run_all.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Ejecución previa aún en curso, se omite este ciclo.\n");
    exit(0);
}

// Días de retención para historial_potencia. Suficiente para ventanas de 6h
// y comparativas de "variaciones de señal"; ajustar si se necesita más rango
// para el histórico de "LOS parcial / Desde".
define('HISTORIAL_POTENCIA_RETENCION_DIAS', 7);

/**
 * Ejecuta un paso nombrado, mide tiempo, captura cualquier excepción para
 * que un fallo en un paso NO detenga los pasos siguientes.
 */
function runStep(string $name, callable $fn): void
{
    $start = microtime(true);
    echo "[" . date('Y-m-d H:i:s') . "] >> Iniciando: $name\n";
    try {
        $result = $fn();
        $elapsed = round(microtime(true) - $start, 2);
        $resumen = is_string($result) ? " - $result" : '';
        echo "[" . date('Y-m-d H:i:s') . "] OK  $name ({$elapsed}s)$resumen\n";
    } catch (\Throwable $e) {
        $elapsed = round(microtime(true) - $start, 2);
        error_log("[run_all] Error en '$name': " . $e->getMessage());
        echo "[" . date('Y-m-d H:i:s') . "] FAIL $name ({$elapsed}s): " . $e->getMessage() . "\n";
    }
}

try {

    // ---- Paso 2: cache de ONUs desautorizadas (para el dashboard) ----
    runStep('unconfigured_cache', function () {
        return require __DIR__ . '/tasks/unconfigured_cache_task.php';
    });

    // ---- Paso 3: eventos de outage (LOS parcial/total, PwrFail, Offline)
    // por puerto GPON. Debe correr DESPUÉS de refresh_db (paso 1), ya que
    // depende del Status de `potencia` recién actualizado por SNMP. ----
    runStep('outage_events', function () {
        return require __DIR__ . '/tasks/outage_events_task.php';
    });

    // ---- Paso 4: purga de historial_potencia para no crecer indefinidamente ----
    runStep('purge_historial_potencia', function () {
        require_once(__DIR__ . '/../db/conn.php');
        $pdo = (new DbConn())->getPdo();
        $dias = HISTORIAL_POTENCIA_RETENCION_DIAS;
        $stmt = $pdo->prepare("DELETE FROM historial_potencia WHERE HFecha < (NOW() - INTERVAL :dias DAY)");
        $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
        $stmt->execute();
        return "{$stmt->rowCount()} filas eliminadas (retención: {$dias} días)";
    });
    
    // ---- Paso 1: refresh_db (SNMP -> DB: status, potencia, historial_potencia,
    // vlans, migraciones, banda ancha) ----
    runStep('refresh_db', function () {
        return require __DIR__ . '/tasks/refresh_db_task.php';
    });

    echo "[" . date('Y-m-d H:i:s') . "] Ciclo completo.\n\n";

} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}
