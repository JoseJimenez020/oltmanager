<?php
require_once(__DIR__ . '../../app/metodos/uplink/uplinkController.php');
$http = $_SERVER['REQUEST_METHOD'];

if ($http === 'GET') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;
    $v = new uplinkController();

    switch ($oper) {
        case 'All':
            $text=$v->uplinkProfilePort($id);
            break;
        default:
            $text = [
                'status' => false,
                'message' => 'fallo'
            ];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}