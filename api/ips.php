<?php
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '../../app/metodos/ips/ipsController.php');
require_once(__DIR__ . '../../app/metodos/logs/logsController.php');
$http=$_SERVER['REQUEST_METHOD'];
if ($http === 'GET') {
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;
    $idOlt = isset($_GET['olt']) ? $_GET['olt'] : null;
    $zona = isset($_GET['zona']) ? $_GET['zona'] : null;

    $olt=new oltProfileController();
    $ips=new ipsController();

    switch ($oper) {
        
        case 'vlanMgmt':
            
            $vlan=$olt->getVlansMgmtByOlt($zona);
            $text = [
                'status' => true,
                'message' => $vlan
            ];
            break;
        case 'ipMgmt':
            $ip=$ips->getIpsByOlt($zona);
            $text = [
                'status' => true,
                'message' => $ip
            ];
            break;
        case 'ipsTable':
            $ip=$ips->getIpsTable($zona);
            $text = [
                'status' => true,
                'ip' => $ip
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