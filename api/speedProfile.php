<?php
require_once(__DIR__ . '../../app/class/Olt.php');
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '../../app/metodos/onuProfile/onuProfileController.php');
require_once(__DIR__ . '../../app/metodos/speedProfile/speedProfileController.php');
require_once(__DIR__ . '../../app/vendor/Telnet.php');
require_once(__DIR__ . '../../app/metodos/logs/logsController.php');
if ($_SERVER['REQUEST_METHOD'] ==='GET') {
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;
    $idSpeed = isset($_GET['speed']) ? $_GET['speed'] : null;
    $idOlt = isset($_GET['olt']) ? $_GET['olt'] : null;
    $profileDb = new speedProfileController();
    $oltDb =new oltProfileController();

    switch ($oper) {
        case 'all':
            $profile=$profileDb->GetSpeedProfile();
            $text = [
                'status' => true,
                'message' => $profile
            ];
            break;
        case 'allUp':
            $olt=$oltDb->GetOne($idOlt);
            $profile=$profileDb->GetSpeedTypeProfile('up',$olt['OltIdApi']);
            $text = [
                'status' => true,
                'message' => $profile
            ];
            break;
        case 'allDown':
            $olt=$oltDb->GetOne($idOlt);
            $profile=$profileDb->GetSpeedTypeProfile('down',$olt['OltIdApi']);
            $text = [
                'status' => true,
                'message' => $profile
            ];
            break;
        case 'one':
            $profile=$profileDb->GetOne($idSpeed);
            $text = [
                'status' => true,
                'message' => $profile
            ];
            break;
        case 'speedFormOnu':
            $o =new onuProfileController();
            $onu = $o->GetResyncOnu($idSpeed);
            $ups = $profileDb->getSpeedFormByIdOnu($idSpeed,'up');
            $downs = $profileDb->getSpeedFormByIdOnu($idSpeed,'down');
            $text = [
                'status' => true,
                'ups' => $ups,
                'downs'=> $downs,
                'up'=>$onu['OntSpeedUp'],
                'down'=>$onu['OntSpeedDown']
            ];
            break;
        default:
            $text = [
                'status' => false,
                'message' => 'error'
            ];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    $oltDb =new oltProfileController();
    $logs = new logsController();
    $idOlt = isset($_POST['olt']) ? $_POST['olt'] : null;
    $id = isset($_POST['profile']) ? $_POST['profile'] : null;
    $oper = isset($_POST['accion']) ? $_POST['accion'] : null;
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $type = isset($_POST['type']) ? $_POST['type'] : null;
    $speed = isset($_POST['speed']) ? $_POST['speed'] : null;
    switch ($oper) {
        case 'add':
            $olt = $oltDb->GetOne($idOlt);
            $trafficTel =new OpenSock($olt['OltIpPrivate']);
            
            $profileDb = new speedProfileController();

            $profileDb->InsertSP($name,$olt['OltIdApi'],$type,$speed);
            $trafficTel->profileSpeed($olt['UserTelnet'],$olt['PassTelnet'],$name,$speed,$type);
            $logs->insertLogs(null,$idOlt,"perfil de velocidad $name nuevo",$_SERVER['REQUEST_METHOD'],false);
            $text = [
                'status' => true,
                'message' => $olt
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

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $oltDb =new oltProfileController();
    $profileDb = new speedProfileController();
    $logs = new logsController();

    $profile=$profileDb->GetOne($_GET['profile']);
    $olt = $oltDb->GetOne($_GET['olt']);
    $trafficTel =new OpenSock($olt['OltIpPrivate']);
    
    $trafficTel->noProfileSpeed($olt['UserTelnet'],$olt['PassTelnet'],$profile['ProfileName'],$profile['Tipo']);
    $profileDb->DeleteOne($_GET['profile']);
    $logs->insertLogs(null,$_GET['olt'],"perfil de velocidad {$profile['ProfileName']}",$_SERVER['REQUEST_METHOD'],false);
    $text = [
        'status' => true,
        'message' => 'ok'
    ];
    header('Content-Type: application/json');
    echo json_encode($text);
}