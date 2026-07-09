<?php
/**
 * cron/update_unconfigured_cache.php
 *
 * Actualiza el conteo de ONUs desautorizadas por OLT en la tabla unconfigured_cache.
 * Este script se ejecuta por CRON del sistema, NUNCA por una petición web.
 *
 */

require_once(__DIR__ . '/../app/metodos/OnuGet.php');
require_once(__DIR__ . '/../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '/../db/conn.php');

set_time_limit(0); // proceso en background: puede tardar lo que necesite

// ---- Lock para evitar ejecuciones solapadas si un ciclo SNMP se alarga ----
$lockFile = __DIR__ . '/update_unconfigured_cache.lock';
$lockHandle = fopen($lockFile, 'c');
if (!$lockHandle || !flock($lockHandle, LOCK_EX | LOCK_NB)) {
    fwrite(STDERR, "[" . date('Y-m-d H:i:s') . "] Ejecución previa aún en curso, se omite este ciclo.\n");
    exit(0);
}

date_default_timezone_set('America/Merida');

try {
    $eonuget = new OnuGet();
    $o       = new oltProfileController();
    $olts    = $o->GetOlt();

    $db  = new DbConn();
    $pdo = $db->getPdo();

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
            error_log("[update_unconfigured_cache] Error en OLT {$zona['OltName']}: " . $e->getMessage());
            // Si esta OLT falla, no tocamos su registro: se queda el último valor válido en caché
            // en vez de mostrar 0 y ensuciar el total.
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
        // y da respiro a otros procesos (ej. load_temperatures.php) que también usan SNMP.
        usleep(200000); // 200ms
    }

    echo "[" . date('Y-m-d H:i:s') . "] Cache actualizada. OLTs ok: $procesadas, con error: $conError\n";

} finally {
    flock($lockHandle, LOCK_UN);
    fclose($lockHandle);
}