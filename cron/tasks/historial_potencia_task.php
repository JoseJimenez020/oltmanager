<?php
// cron/tasks/historial_potencia_task.php
// Se ejecuta DESPUÉS de refresh_db_task.php, reutilizando $GLOBALS['ultimoProfile']
// (el array que devuelve profileOnu::getProfiles(), ya con 'rx' e 'index' por ONU)

if (empty($GLOBALS['ultimoProfile'])) {
    error_log("[historial_potencia] No hay datos de perfil disponibles, se omite este ciclo.");
    return;
}

require_once(__DIR__ . '/../../db/conn.php');
$db = new DbConn();
$pdo = $db->getPdo();

$stmt = $pdo->prepare(
    'INSERT INTO historial_potencia (IdOnu, RxOnu, HFecha) VALUES (:id, :rx, :fecha)'
);

date_default_timezone_set('America/Merida');
$fecha = date('Y-m-d H:i:s');

$insertados = 0;
foreach ($GLOBALS['ultimoProfile'] as $onu) {
    // 'sn' viene en el perfil; hace falta mapear sn -> OntId real en DB
    // (esto requiere el mismo array self::$gpon que usa profileOnu::formatOnu())
    if (empty($onu['ontId'])) continue; // ver nota abajo sobre cómo resolverlo
    try {
        $stmt->execute([':id' => $onu['ontId'], ':rx' => $onu['rx'], ':fecha' => $fecha]);
        $insertados++;
    } catch (\Throwable $e) {
        error_log("[historial_potencia] Fallo insertando OntId {$onu['ontId']}: " . $e->getMessage());
    }
}
echo "Historial de potencia: $insertados registros insertados.\n";