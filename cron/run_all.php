<?php
/**
 * cron/run_all.php
 * DEPLOY: C:\xampp\htdocs\oltmanager\cron\run_all.php
 *
 * Orquestador único para las tareas periódicas de OLT Manager.
 *
 * CAMBIOS EN ESTA VERSIÓN:
 *   1. Orden corregido: refresh_db corre PRIMERO (actualiza Status/Potencia),
 *      luego unconfigured_cache, luego outage_events (que depende del
 *      Status recién actualizado — antes corría con datos del ciclo previo).
 *   2. purge_historial_potencia ahora borra EN LOTES (antes era un solo
 *      DELETE sin límite que, sobre una tabla de millones de filas y
 *      cross-VM, probablemente nunca terminaba a tiempo — por eso quedaban
 *      registros de semanas atrás pese a la retención de 7 días).
 *   3. No se agrega aquí la tarea de variaciones_senal_cache: esa corre
 *      cada hora vía su propia tarea de Task Scheduler
 *      (cron/tasks/variaciones_senal_cache_task.php), independiente de
 *      este ciclo de ~7 min, tal como se acordó.
 *
 * INSTALACION (sin cambios, sigue igual):
 *   schtasks /create /tn "OltManager RunAll" /tr "C:\xampp\htdocs\oltmanager\cron\run_all.bat" /sc minute /mo 6 /f
 **/

set_time_limit(0);
date_default_timezone_set('America/Merida');

$lockFile = __DIR__ . '/run_all.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Ejecución previa aún en curso, se omite este ciclo.\n");
    exit(0);
}

define('HISTORIAL_POTENCIA_RETENCION_DIAS', 7);

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

    // ---- Paso 1: refresh_db (SNMP -> DB). Debe ir PRIMERO: todo lo demás
    // depende de Status/Potencia/historial_potencia recién actualizados ----
    runStep('refresh_db', function () {
        return require __DIR__ . '/tasks/refresh_db_task.php';
    });

    // ---- Paso 2: cache de ONUs desautorizadas (para el dashboard) ----
    runStep('unconfigured_cache', function () {
        return require __DIR__ . '/tasks/unconfigured_cache_task.php';
    });

    // ---- Paso 3: eventos de outage. Ahora sí corre DESPUÉS de refresh_db,
    // usando el Status del ciclo actual y no el del ciclo anterior ----
    runStep('outage_events', function () {
        return require __DIR__ . '/tasks/outage_events_task.php';
    });

    // ---- Paso 4: purga de historial_potencia EN LOTES. Antes era un solo
    // DELETE sin límite: sobre una tabla de millones de filas cruzando la
    // VM de BD, ese DELETE probablemente nunca alcanzaba a completar
    // dentro del ciclo, dejando basura de semanas pese a la retención
    // configurada de 7 días. Ahora borra en bloques de 5000 hasta agotar,
    // reiniciando el timer entre lotes igual que en bandWith/potencia. ----
    runStep('purge_historial_potencia', function () {
        require_once(__DIR__ . '/../db/conn.php');
        $pdo = (new DbConn())->getPdo();
        $dias = HISTORIAL_POTENCIA_RETENCION_DIAS;
        $totalEliminadas = 0;
        $stmt = $pdo->prepare(
            "DELETE FROM historial_potencia WHERE HFecha < (NOW() - INTERVAL :dias DAY) LIMIT 5000"
        );
        do {
            $stmt->bindValue(':dias', $dias, PDO::PARAM_INT);
            $stmt->execute();
            $borradas = $stmt->rowCount();
            $totalEliminadas += $borradas;
            set_time_limit(300);
        } while ($borradas > 0);
        return "{$totalEliminadas} filas eliminadas en total (retención: {$dias} días)";
    });

    echo "[" . date('Y-m-d H:i:s') . "] Ciclo completo.\n\n";

} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}