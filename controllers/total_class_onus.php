<?php

require_once(__DIR__ . '../../app/metodos/OnuDb.php');
$query = new OnuDb();

$sql_total = $query->GetTotalOnus();

foreach ($sql_total as $row) {
    $data[] = [
        'totalUnconfigured' => $row['total_unconfigured'] . " ONUS",
        'totalOnline' => $row['total_online'] . " ONUS",
        'descripcionOk' => "Total autorizados: " . $row['total_ok'],
        'totalOff' => $row['total_off'] . " ONUS",
        'descripcionOffline' => "PwrFail: " . $row['total_pfail'] . " LOS: " . $row['total_los'] . " N/A: " . $row['total_offline'],
        'totalLow' => $row['total_low'] . " ONUS",
        'descripcionLow' => "Debil: " . $row['total_warning'] . " Critico: " . $row['total_critical']
    ];
}
// Retorna los datos como JSON
header('Content-Type: application/json');
echo json_encode($data);
