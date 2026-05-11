<?php
include 'query_db.php';

$olt = isset($_GET['olt']) ? $_GET['olt'] : null;
$card = isset($_GET['card']) ? $_GET['card'] : null;

$puertos = $Onus->GetPuertos($olt, $card);

// Simula los datos consultados desde la base de datos.
$data = [];

// Tu lógica para llenar $data con datos reales.
foreach ($puertos as $row) {
    $data[] = [
        'puerto' => $row['IndexPort']
    ];
}

// Retorna los datos como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>