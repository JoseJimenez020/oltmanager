<?php
/**
 * cron/tasks/unconfigured_cache_task.php
 *
 * Versión para el orquestador de cron/update_unconfigured_cache.php.
 * Misma lógica exacta (conteo de ONUs desautorizadas por OLT, cacheado en
 * unconfigured_cache para que index.php/total_class_onus.php no golpeen SNMP
 * en cada carga de página), pero SIN su propio lock file: run_all.php ya
 * garantiza que esto no corre en paralelo con nada más.
 *
 * NOTA: cron/update_unconfigured_cache.php (el original standalone, con su
 * propio flock) se deja intacto por si se necesita correr este paso aislado
 * fuera del orquestador, por ejemplo para debug manual.
 */

require_once(__DIR__ . '/../../app/metodos/OnuGet.php');
require_once(__DIR__ . '/../../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '/../../db/conn.php');

date_default_timezone_set('America/Merida');

$eonuget = new OnuGet();
$o       = new oltProfileController();
$olts    = $o->GetOlt();

$pdo = (new DbConn())->getPdo();

$stmt = $pdo->prepare(
    'INSERT INTO unconfigured_cache (OltIdApi, OltName, TotalUnconf, LastUpdated)
     VALUES (:oltId, :oltName, :total, :date)
     ON DUPLICATE KEY UPDATE
        OltName = :oltName2,
        TotalUnconf = :total2,
        LastUpdated = :date2'
);

$procesadas = 0;
$conError   = 0;

foreach ($olts as $zona) {
    $count = 0;
    try {
        $res = $eonuget->Data($zona['OltName'], "OnuSnUncf");
        foreach ($res as $key => $value) {
            if ($key != "NumeroError") {
                $count++;
            }
        }
    } catch (\Throwable $e) {
        $conError++;
        error_log("[unconfigured_cache_task] Error en OLT {$zona['OltName']}: " . $e->getMessage());
        // Si esta OLT falla, no tocamos su registro: se queda el último valor
        // válido en caché en vez de mostrar 0 y ensuciar el total.
        continue;
    }

    $date = date("Y-m-d H:i:s");
    $stmt->execute([
        ':oltId'    => $zona['OltIdApi'],
        ':oltName'  => $zona['OltName'],
        ':total'    => $count,
        ':date'     => $date,
        ':oltName2' => $zona['OltName'],
        ':total2'   => $count,
        ':date2'    => $date,
    ]);

    $procesadas++;

    // Pequeña pausa entre OLTs: evita saturar el enlace/sesión SNMP
    // y da respiro a otros procesos que también usan SNMP.
    usleep(200000); // 200ms
}

return "OLTs ok: $procesadas, con error: $conError";
