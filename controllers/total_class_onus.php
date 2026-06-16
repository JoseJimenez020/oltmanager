<?php
require_once(__DIR__ . '../../app/metodos/OnuDb.php');
require_once(__DIR__ . '../../app/metodos/OnuGet.php');
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');

$query = new OnuDb();
$sql_total = $query->GetTotalOnus();

// Contar desautorizadas en tiempo real desde SNMP
$eonu = new OnuGet();
$o = new oltProfileController();
$olts = $o->GetOlt();
$totalUnconfigured = 0;

foreach ($olts as $zona) {
    $res = $eonu->Data($zona['OltName'], "OnuSnUncf");
    foreach ($res as $key => $value) {
        if ($key != "NumeroError") {
            $totalUnconfigured++;
        }
    }
}

foreach ($sql_total as $row) {
    $data[] = [
        'totalUnconfigured' => $totalUnconfigured . " ONUS",
        'totalOnline' => $row['total_online'] . " ONUS",
        'descripcionOk' => "Total autorizados: " . $row['total_ok'],
        'totalOff' => $row['total_off'] . " ONUS",
        'descripcionOffline' => "PwrFail: " . $row['total_pfail'] . " LOS: " . $row['total_los'] . " N/A: " . $row['total_offline'],
        'totalLow' => $row['total_low'] . " ONUS",
        'descripcionLow' => "Debil: " . $row['total_warning'] . " Critico: " . $row['total_critical']
    ];
}

header('Content-Type: application/json');
echo json_encode($data);
