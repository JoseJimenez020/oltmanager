<?php
include 'query_db.php';

$olt= isset($_GET['olt']) ? $_GET['olt'] : null;

$tarjetas = $Onus->GetTarjetas($olt);

// Simula los datos consultados desde la base de datos.
$data = [];

// Tu lógica para llenar $data con datos reales.
foreach ($tarjetas as $row) {
    $data[] = [
        'tarjeta' => $row['IndexCard']
    ];
}

// Retorna los datos como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>