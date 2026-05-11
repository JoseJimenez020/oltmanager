<?php
require_once(__DIR__ . '../../app/metodos/vlanProfile/vlanProfileController.php');

$http = $_SERVER['REQUEST_METHOD'];

if ($http === 'GET') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;
    $v = new vlanProfileController();

    switch ($oper) {
        case 'formDhcp':
            $vlan = $v->GetVlansOnuInfo($id);
            $vlans = $v->getVlansFormById($id);
            $text = [
                'status' => true,
                'vlan' => $vlan,
                'vlans'=>$vlans 
            ];
            break;
        case 'All':
            $text=$v->getAllVlansOlt($id);
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
if ($http === 'POST') {
    $oper = json_decode(file_get_contents('php://input'),true);
    $accion = (empty($oper['accion'])) ? null : $oper['accion'];
    $v = new vlanProfileController();
    switch ($accion) {
        case 'addVlan':
            $text = $v->insertVlanOlt($oper);
            break;
        default:
            $text=[
                'status'=>false,
                'entity'=>'fallo'
            ];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}
if ($http === 'DELETE') {
    $oper = json_decode(file_get_contents('php://input'),true);
    $accion = $oper['accion'] ?? null;
    $v = new vlanProfileController();
    switch ($accion) {
        case 'deleteVlan':
            $text = $v->deleteVlanOlt($oper);
            break;
        default:
            $text=[
                'status'=>false,
                'entity'=>'fallo'
            ];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}