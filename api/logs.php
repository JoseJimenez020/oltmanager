<?php
require_once(__DIR__ . '../../app/metodos/logs/logsController.php');

$http = $_SERVER['REQUEST_METHOD'];
if ($http == 'GET') {
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;
    $idOnu = isset($_GET['onu']) ? $_GET['onu'] : null;
    $l = new logsController();
    switch ($oper) {
        case 'general':
            $r = $l->getLogsGeneral();
            $text = [
                'status' => true,
                'message' => $r
            ];
            break;
        case 'onu':
            $r = $l->getLogsOnu($idOnu);
            $text = [
                'status' => true,
                'message' => $r
            ];
            break;
        default:
            # code...
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}