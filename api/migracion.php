<?php
require_once(__DIR__ . '../../app/metodos/migracion/migracionController.php');
$http = $_SERVER['REQUEST_METHOD'];
if ($http === 'GET') {
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;

    $m = new migracionController();
    switch ($oper) {
        case 'all':
            $r = $m->getMigracions();
            $text = [
                'status' => true,
                'message' => $r
            ];
            break;
        
        default:
            $text = [
                'status' => false,
                'message' => ''
            ];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}
if ($http === 'POST') {
    $oper = json_decode(file_get_contents('php://input'),true);
    $m = new migracionController();

    switch ($oper['accion']) {
        case 'migrar':
            
            $text=[
                'status'=>true,
                'message'=>$m->updateMigracionMany($oper['Id'])
            ];
            break;
        
        default:
            $text=[
                'status'=>false,
                'message'=>''
            ];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}