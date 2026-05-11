<?php
// Habilitar reporte de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once(__DIR__ . '../../app/metodos/OnuGet.php');
$eonuget = new OnuGet();

// Recibe los parámetros desde la solicitud GET
$pass = isset($_GET['pass']) ? $_GET['pass'] : null;
$index = isset($_GET['index']) ? $_GET['index'] : null;

if ($pass === null || $index === null) {
    // Retorna un error si faltan parámetros
    http_response_code(400);
    echo json_encode(['error' => 'Faltan parámetros pass o index.']);
    exit;
}

try {
    // Obtiene los datos desde la lógica implementada
    $data=$eonuget->setAdminStateOnu($index, $pass, 1);

    // Simula los datos consultados desde la base de datos (ajusta según tu implementación)
    $data = [
        'status' => $status,
        'distancia' => $distancia,
        'potencia' => number_format($potencia / 1000, 2)
    ];

    // Retorna los datos como JSON
    header('Content-Type: application/json');
    echo json_encode($data);
} catch (Exception $e) {
    // Manejo de errores
    http_response_code(500);
    echo json_encode(['error' => 'Error al procesar la solicitud.', 'details' => $e->getMessage()]);
}
?>