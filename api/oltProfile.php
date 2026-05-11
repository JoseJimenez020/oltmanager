<?php
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '../../app/metodos/logs/logsController.php');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;
    $idOlt = isset($_GET['olt']) ? $_GET['olt'] : null;
    $oltDb=new oltProfileController();

    switch ($oper) {
        
        case 'all':
            $olts=$oltDb->GetOlt();
            $text = [
                'status' => true,
                'message' => $olts
            ];
            break;
        case 'one':
            $olt=$oltDb->GetOne($idOlt);
            $text = [
                'status' => true,
                'message' => $olt
            ];
            break;
        case 'listName':
            $olt=$oltDb->getOltList();
            $text = [
                'status' => true,
                'olt' => $olt
            ];
            break;
        default:
            $text = [
                'status' => false,
                'message' => "Fallo al recibir datos"
            ];
            break;
    }
    
    
    header('Content-Type: application/json');
    echo json_encode($text);
}
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $oltDb=new oltProfileController();
    $logs = new logsController();
    $form = json_decode(file_get_contents('php://input'),true);
    
    $r = $oltDb->UpdateOltProfile($form);
    $logs->insertLogs(null,$form['id'],"informacion de olt",$_SERVER['REQUEST_METHOD'],false);
    $text = [
        'status' => true,
        'message' => $r
    ];
    header('Content-Type: application/json');
    echo json_encode($text);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once(__DIR__ . '../../app/metodos/onuProfile/profileOnu.php');
    $oper = json_decode(file_get_contents('php://input'),true);
    switch ($oper['accion']) {
        case 'inOnuOlt':
            $m= new profileOnu();
            $m->setOlt();
            
            $profile = $m->getProfiles();
            $onus = $m->insertOnus($profile);
            $m->setGponOnu();
            $potencia = $m->insertPotencia($profile);
            $vlans = $m->getVlans();
            $vlan = $m->insertVlans($vlans);
            $text=[
                'status'=>$vlan,
                'entity'=>''
            ];
            break;
        
        default:
            $text=[
                'status'=>false,
                'entity'=>''
            ];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}