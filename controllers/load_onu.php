<?php
require_once(__DIR__ . '../../app/metodos/OnuGet.php');
require_once(__DIR__ . '../../app/metodos/OnuDb.php');
$eonu=new OnuGet();

$pass = isset($_GET['pass']) ? $_GET['pass'] : null;
$index = isset($_GET['index']) ? $_GET['index'] : null;

if (!$pass || !$index) {
    echo json_encode(['error' => 'Parámetros faltantes: pass e index son requeridos.']);
    exit;
}

$status = $eonu->GetOne($index,$pass, 'OnuStatus');
$rx = $eonu->GetOne($index,$pass, 'OnuRxOlt');

// Tu lógica para llenar $data con datos reales.
    $data[] = [
        'status' => $pass,
        'potencia' => $rx
    ];

// Retorna los datos como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>