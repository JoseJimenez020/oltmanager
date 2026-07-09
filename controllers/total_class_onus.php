<?php
require_once(__DIR__ . '../../app/metodos/OnuDb.php');
require_once(__DIR__ . '../../db/conn.php');

$query = new OnuDb();
$sql_total = $query->GetTotalOnus();

// El conteo de desautorizadas ya NO se calcula por SNMP en cada carga.
// Se lee de la caché que llena cron/update_unconfigured_cache.php.
$db  = new DbConn();
$pdo = $db->getPdo();
$cacheStmt = $pdo->query(
    "SELECT SUM(TotalUnconf) AS total, MAX(LastUpdated) AS last_updated FROM unconfigured_cache"
);
$cache = $cacheStmt->fetch(PDO::FETCH_ASSOC);
$totalUnconfigured = $cache['total'] !== null ? (int) $cache['total'] : 0;

$data = [];
foreach ($sql_total as $row) {
    $data[] = [
        'totalUnconfigured'  => $totalUnconfigured . " ONUS",
        'totalOnline'        => $row['total_online'] . " ONUS",
        'descripcionOk'      => "Total autorizados: " . $row['total_ok'],
        'totalOff'           => $row['total_off'] . " ONUS",
        'descripcionOffline' => "PwrFail: " . $row['total_pfail'] . " LOS: " . $row['total_los'] . " N/A: " . $row['total_offline'],
        'totalLow'           => $row['total_low'] . " ONUS",
        'descripcionLow'     => "Debil: " . $row['total_warning'] . " Critico: " . $row['total_critical']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
