<?php
require_once(__DIR__ . '../../app/vendor/Telnet.php');
require_once(__DIR__ . '../../app/class/Olt.php');
require_once(__DIR__ . '../../app/metodos/onuType/onuTypeController.php');
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '../../app/metodos/logs/logsController.php');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;

    $typeDb = new onuTypeController();
    switch ($oper) {
        case 'all':
            $type = $typeDb->GetOnuType();

            $text = [
                'status' => true,
                'message' => $type
            ];
            break;

        default:
            $text = [
                'status' => true,
                'message' => 'ERROR'
            ];
            break;
    }
    header(header: 'Content-Type: application/json');
    echo json_encode($text);
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oper = isset($_POST['accion']) ? $_POST['accion'] : null;
    $idType = isset($_POST['type']) ? $_POST['type'] : null;
    $idOlt = isset($_POST['olt']) ? $_POST['olt'] : null;
    //insert
    $name = isset($_POST['name']) ? $_POST['name'] : null;
    $pon = isset($_POST['pon']) ? $_POST['pon'] : null;
    $cap = isset($_POST['cap']) ? $_POST['cap'] : null;
    $eth = isset($_POST['eth']) ? $_POST['eth'] : null;
    $wifi = isset($_POST['wifi']) ? $_POST['wifi'] : null;
    $pots = isset($_POST['pots']) ? $_POST['pots'] : null;

    $logs = new logsController();
    $oltDb = new oltProfileController();
    $olts = $oltDb->GetOlt();
    switch ($oper) {
        case 'add':
            foreach ($olts as $olt) {
                $typeDb = new onuTypeController($olt['OltIpPrivate'], $olt['UserTelnet'], $olt['PassTelnet']);
                $typeDb->typeAdd($name, $eth, $wifi, $pots);
                $logs->insertLogs(null, $olt['OltIdApi'], "Nuevo tipo Onu", $_SERVER['REQUEST_METHOD'], false);
            }
            $typeDb = new onuTypeController();
            $insert = $typeDb->InsertType($name, $pon, $cap, $eth, $wifi, $pots);

            $text = [
                'status' => true,
                'message' => $insert
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
    $idType = isset($_GET['type']) ? $_GET['type'] : null;

    $typeDb = new onuTypeController();
    $oltDb = new oltProfileController();
    $logs = new logsController();

    $olts = $oltDb->GetOlt();
    $type = $typeDb->GetOne($idType);

    foreach ($olts as $olt) {
        $typeT = new onuTypeController($olt['OltIpPrivate'], $olt['UserTelnet'], $olt['PassTelnet']);
        $typeT->noTypeAdd($type['OnuTypeName']);
        $logs->insertLogs(null, $olt['OltIdApi'], "tipo en Onu", $_SERVER['REQUEST_METHOD'], false);
    }

    $typeDb->DeleteOne($idType);

    $text = [
        'status' => true,
        'message' => 'exito'
    ];
    header('Content-Type: application/json');
    echo json_encode($text);
}