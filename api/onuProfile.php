<?php
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 0);
ini_set('log_errors',     1);
ini_set('error_log',      (__DIR__ . '../../logs/php_errors.log'));
error_reporting(E_ALL);

// 2) Buffer output so stray HTML/PHP warnings get tossed
ob_start();

// 3) Catch fatal errors (E_ERROR, E_PARSE, etc.) and return JSON
register_shutdown_function(function() {
    $err = error_get_last();
    if ($err && in_array($err['type'], [E_ERROR, E_PARSE, E_CORE_ERROR, E_COMPILE_ERROR])) {
        ob_end_clean();
        http_response_code(500);
        echo json_encode([
            'status'  => false,
            'message' => 'Error interno en el servidor'
        ]);
        exit;
    }
});


require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '../../app/metodos/onuProfile/onuProfileController.php');
require_once(__DIR__ . '../../app/metodos/onuProfile/ipMac.php');
require_once(__DIR__ . '../../app/metodos/vlanProfile/vlanProfileController.php');
require_once(__DIR__ . '../../app/metodos/onuType/onuTypeController.php');
require_once(__DIR__ . '../../app/metodos/ips/ipsController.php');
require_once(__DIR__ . '../../app/metodos/potencia/potenciaController.php');
require_once(__DIR__ . '../../app/metodos/logs/logsController.php');
require_once(__DIR__ . '../../app/metodos/OnuDb.php');
require_once(__DIR__ . '../../app/vendor/Telnet.php');

try{
if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $oper = json_decode(file_get_contents('php://input'), true);
    $onu = new onuProfileController();
    $olt = new oltProfileController();
    $eonu = new OnuDb();
    $ips = new ipsDb();
    $logs= new logsController();
    switch ($oper['accion']) {
        case 'resync':
            $resync = $onu->GetResyncOnu($oper['id']);
            $vlan = $onu->GetVlanOnu($oper['id']);
            $tel = new OpenSock($resync['OltIpPrivate']);
            $tel->deleteOnu($resync);
            $tel->close();
            $tel->putResyncTel($resync, $vlan);
            $tel->close();
            $onu->DeleteVlansOnu($vlan);
            $logs->insertLogs($resync,$resync['OltIdApi'],'resincroniza',$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            $text = [
                'status' => true,
                'message' => 'exito'
            ];
            break;
        case 'disable':
            $resync = $onu->GetResyncOnu($oper['id']);
            $tel = new OpenSock($resync['OltIpPrivate']);
            $tel->putDisableTel($resync, $oper['metodo']);
            $tel->close();
            if ($oper['metodo'] == 'disable') {
                $logs->insertLogs($resync,$resync['OltIdApi'],"{$oper['metodo']} en onu",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }else {
                $logs->insertLogs($resync,$resync['OltIdApi'],"{$oper['metodo']} en onu",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }
            $text = [
                'status' => true,
                'message' => 'exito'
            ];
            break;
        case 'reiniciar':
            $resync = $onu->GetResyncOnu($oper['id']);
            $tel = new OpenSock($resync['OltIpPrivate']);
            $tel->putRebootTel($resync);
            $tel->close();
            $logs->insertLogs($resync,$resync['OltIdApi'],'reinicia',$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            $text = [
                'status' => true,
                'message' => "exito"
            ];
            break;
        case 'restore':
            $resync = $onu->GetResyncOnu($oper['id']);
            $tel = new OpenSock($resync['OltIpPrivate']);
            $tel->putRestoreTel($resync);
            $tel->close();
            $logs->insertLogs($resync,$resync['OltIdApi'],'restablece por defecto',$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            $text = [
                'status' => true,
                'message' => "exito"
            ];
            break;
        case 'speedProfile':
            $eonu->UpdateOnuSpeed($oper['up'], $oper['down'], $oper['id']);
            $resync = $onu->GetResyncOnu($oper['id']);
            $tel = new OpenSock($resync['OltIpPrivate']);
            $tel->putSpeedProTel($resync);
            $tel->close();
            $logs->insertLogs($resync,$resync['OltIdApi'],"up {$resync['Up']} down {$resync['Down']}",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            $text = [
                'status' => true,
                'message' => "exito"
            ];
            break;

        case 'onuMode':
            $resync = $onu->GetResyncOnu($oper['id']);
            $vlan = $onu->GetVlanOnu($oper['id']);
            $tel = new OpenSock($resync['OltIpPrivate']);
            $tel->BridgingEthMng($resync, $oper['onu_mode'], $vlan[0]['Vlan']);
            $tel->close();
            $tel->VlanFilterMng($resync, $vlan[0]['Vlan']);
            $tel->close();
            for ($i = 1; $i <= $resync['EthernetPorts']; $i++) {
                $tel->DhcpIpVlanPortMng($resync, $oper['onu_mode'], $vlan[0]['Vlan'], $i);
                $tel->close();
            }
            if ($oper['onu_mode'] == 'bridging') {
                $logs->insertLogs($resync,$resync['OltIdApi'],"Modo puente",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }else {
                $logs->insertLogs($resync,$resync['OltIdApi'],"Modo routing",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }
            
            $text = [
                'status' => true,
                'message' => 'exito'
            ];
            break;
        case 'tr069':
            $resync = $onu->GetResyncOnu($oper['id']);
            $tel = new OpenSock($resync['OltIpPrivate']);
            $v=$olt->getVlanMgmtOlt($resync['OntOlt']);
            $ip=$ips->getIpOlt($oper['mgmtIp']);
            if ($oper['mgmt'] == 'disable') {
                $tel->deleteTR069MgmtTel($resync);
                $tel->close();
            }
            if ($oper['mgmt'] == 'static') {
                $tel->putTR069ProfileTel($resync,$v[$oper['mgmtVlan']][0],$ip);
                $tel->close();
            }
            $mgmt=5;
            for ($mgmt; $mgmt <=8 ; $mgmt++) { 
                if ($oper['access'] == 'default') {
                    $tel->putOnuMgmRemoteAccTel($resync,$mgmt,'default');
                }
                if ($oper['access'] == 'allow' && $mgmt == 5) {
                    $tel->putOnuMgmRemoteAccTel($resync,$mgmt,'tr069');
                }elseif ($oper['access'] == 'allow' && $mgmt != 5){
                    $tel->putOnuMgmRemoteAccTel($resync,$mgmt,'tr069');
                }
            }
            if ($oper['mgmt'] == 'static') {
                $logs->insertLogs($resync,$resync['OltIdApi'],"Enable TR069",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }else {
                $logs->insertLogs($resync,$resync['OltIdApi'],"Disable TR069",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }
            $text = [
                'status' => true,
                'message' => 'exito'
            ];
            break;
        case 'gestion':
            $vlan = $oper['vlan'];
            $conf = $oper['config'];
            $mgmt = 1;
            $resync=$onu->GetResyncOnu($oper['id']);
            $vlanOltOnu=$eonu->GetOltVlanOnu($resync['OntOlt']);
            $vlansOnuDb=$eonu->GetVlansOnuInfo($oper['id']);
            $vlanDb=$vlansOnuDb[0]['Vlan'];
            $idDVlan=$vlansOnuDb[0]['IdVlanOnu'];
            for ($mgmt; $mgmt <=4 ; $mgmt++) { 
                if ($conf == 'default') {
                    $tel=new OpenSock($resync['OltIpPrivate']);
                    $res=$tel->putOnuMgmRemoteAccTel($resync,$mgmt,$conf);
                    //echo $conf . " count: $mgmt<br>";
                }
                if ($conf == 'dhcp' && $mgmt == 1) {
                    $tel=new OpenSock($resync['OltIpPrivate']);
                    $res=$tel->putOnuMgmRemoteAccTel($resync,$mgmt,$conf);
                    //echo $conf . " count: $mgmt<br>";
                }elseif ($conf == 'dhcp' && $mgmt != 1){
                    $tel=new OpenSock($resync['OltIpPrivate']);
                    $res=$tel->putOnuMgmRemoteAccTel($resync,$mgmt,$conf);
                    //echo $conf . " round: $mgmt<br>";
                }
            }
            if ($vlan != $vlanDb) {
                foreach ($vlanOltOnu as $vlanOlt) {
                    if ($vlanOlt['Vlan'] == $vlan) {
                        $idVlanOlt=$vlanOlt['VlanId'];
                    }
                }
                $eonu->DeleteAttachedVlanDb($idDVlan);
                $eonu->CommitInsertMainVlan($id,$idVlanOlt);
                $tel=new OpenSock($resync['OltIpPrivate']);
                $res=$tel->putOnuMgmtAttVlanTel($resync,$vlan,$vlanDb);    
            }
            $tel=new OpenSock($resync['OltIpPrivate']);
            $res=$tel->putOnuMgmTel($resync,$vlan,$conf);
            if ($conf == 'default') {
                $logs->insertLogs($resync,$resync['OltIdApi'],"DHCP desactivado",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }else {
                $logs->insertLogs($resync,$resync['OltIdApi'],"DHCP activado",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }
            $text = [
                'status' => true,
                'message' => 'exito'
            ];
            break;
        case 'attachedVlan':
            $resync=$onu->GetResyncOnu($oper['id']);
            $vlans = $oper['vlans'];
            $vlanAdd=array();
            //$vlans=explode(" ",$vlansForm);
            $vlanOltOnu=$eonu->GetOltVlanOnu($resync['OntOlt']);
            $vlansOnuDb=$eonu->GetVlansOnuInfo($oper['id']);
            //obtener arreglo de vlans en db para comparar
            $vlansOnu = array_map(function($item) {
                return $item["Vlan"];
            }, $vlansOnuDb);
            $servicePortOnu = array_map(function($item) {
                return $item["ServicePortOnu"];
            }, $vlansOnuDb);
            //encontrar coincidencia de form con db 
            foreach ($vlans as $key => $value) {
                if (!in_array($value,$vlansOnu)) {
                    $vlanAdd[]=$value;
                }
            }
            $deleteDb=array_diff($vlansOnu,$vlans);
            //true si existe algo que eliminar en db
            if ($deleteDb) {
                //echo "eliminar db<br>";
                $deleteDb=array_values($deleteDb);
                //var_dump($deleteDb);
                for ($i=0; $i <count($deleteDb) ; $i++) { 
                    foreach ($vlansOnuDb as $dvlanOlt) {
                        if ($dvlanOlt['Vlan'] == $deleteDb[$i]) {
                            $tel=new OpenSock($resync['OltIpPrivate']);
                            $res=$tel->deleteOnuAttVlanTel($resync,$dvlanOlt['ServicePortOnu'],$dvlanOlt['Vlan']);
                            $eonu->DeleteAttachedVlanDb($dvlanOlt['IdVlanOnu']);
                            $logs->insertLogs($resync,$resync['OltIdApi'],"vlan {$dvlanOlt['Vlan']} eliminada",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
                            //echo $dvlanOlt['IdVlanOnu'].'<br>';
                        }
                    }
                }   
            }
            //true si se va anadir a olt
            if ($vlanAdd) {
                //echo 'anadir olt<br>';
                $spOnu=end($servicePortOnu);
                $vlanDbId=null;
                //recorremos las vlans por anadir
                for ($i=0; $i <count($vlanAdd) ; $i++) { 
                    $spOnuLenght=strlen($spOnu);
                    //obtener el servicePort de la onu
                    if ($spOnuLenght > 1) {
                        $spOnu++;
                    }else {
                        $spOnu=11;
                    }
                    foreach ($vlanOltOnu as $vlanOlt) {
                        if ($vlanOlt['Vlan'] == $vlanAdd[$i]) {
                            $vlanAttachedInsert=$vlanOlt['Vlan'];
                            $vlanDbId=$vlanOlt['VlanId'];
                        }
                    }
                    $tel=new OpenSock($resync['OltIpPrivate']);
                    $re=$tel->putOnuAttVlanTel($resync,$spOnu,$vlanAttachedInsert);
                    $eonu->CommitInsertAttachedVlan($oper['id'],$spOnu,$vlanDbId);
                    $logs->insertLogs($resync,$resync['OltIdApi'],"vlan {$vlanAttachedInsert} agregada",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
                }
            }
            $text = [
                'status' => true,
                'message' => 'exito'
            ];
            break;
        case 'wifi':
            $resync=$onu->GetResyncOnu($oper['id']);
            $tel=new OpenSock($resync['OltIpPrivate']);
            $tel->onuWifi($resync,$oper['wifi']);
            if ($oper['wifi'] == 'enable') {
                $logs->insertLogs($resync,$resync['OltIdApi'],"wifi enable",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }else {
                $logs->insertLogs($resync,$resync['OltIdApi'],"wifi disable",$_SERVER['REQUEST_METHOD'],true,$oper['id']);
            }
            
            $text = [
                'status' => true,
                'message' => 'exito'
            ];
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
elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oper = json_decode(file_get_contents('php://input'), true);
    $eonu = new OnuDb();
    $o = new oltProfileController();
    $t = new onuTypeController();
    $onuP=new onuProfileController();
    $pot =new potenciaController();
    $logs=new logsController();
    switch ($oper['accion']) {
        case 'Auth':
            $olt = $o->GetOne($oper['olt']);
            $pos = $eonu->GetPosGpon($oper['tarjeta'], $oper['puerto'], $oper['olt']);
            $gponId = $eonu->GetGponId($oper['tarjeta'], $oper['puerto']);
            $up = $eonu->GetSpeedProfileById($oper['up']);
            $down = $eonu->GetSpeedProfileById($oper['down']);
            $vlan = $eonu->GetVlanOltById($oper['vlan']);
            $type = $t->GetOne($oper['tipo']);
            $tel = new OpenSock($olt['OltIpPrivate']);
            $tel->putInsertTel(
                $olt['UserTelnet'],
                $olt['PassTelnet'],
                $oper['tarjeta'],
                $oper['puerto'],
                $pos,
                $type,
                $oper['serie'],
                $oper['nombre'],
                $oper['comentario'],
                $up['ProfileName'],
                $down['ProfileName'],
                $vlan['Vlan'],
                $oper['mode']
            );
            $onuP->insertOnu(
                $gponId['IdOlt'],
                $oper['nombre'],
                $type['OnuTypeName'],
                $oper['serie'],
                $oper['comentario'],
                $oper['olt'],
                $pos,
                $oper['up'],
                $oper['down']
            );
            $idOnuDb = $onuP->getOnuBySn($oper['serie']);
            $eonu->InsertVlanOnuInfo($idOnuDb['OntId'], $oper['vlan']);
            $pot->insertOnePotencia($idOnuDb['OntId']);
            $logs->insertLogs($idOnuDb,$oper['olt'],'Autorizacion cliente',$_SERVER['REQUEST_METHOD'],true,$idOnuDb['OntId']);
            $text = [
                'ok' => true,
                'id' => "{$idOnuDb['OntId']}"
            ];
            break;
        default:
            $text = [
                'ok' => false,
                'message' => 'fallo'
            ];
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}
elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $accion = isset($_GET['accion']) ? $_GET['accion'] : null;
    $onu = new onuProfileController();
    $eonu = new OnuDb();
    $logs=new logsController();

    switch ($accion) {
        case 'onu':
            $resync = $onu->GetResyncOnu($id);
            //validar eliminacion de onu
            $tel = new OpenSock($resync['OltIpPrivate']);
            $tel->deleteOnu($resync);
            $tel->close();
            //elimina base datos
            $onu->DeleteVlan($id);
            $onu->DeleteBandWith($id);
            $onu->DeletePotencia($id);
            sleep(1);
            $onu->DeleteOnu($id);
            $logs->insertLogs($resync,$resync['OltIdApi'],'Onu de la olt',$_SERVER['REQUEST_METHOD']);
            $text = [
                'status' => true,
                'message' => 'exito'
            ];
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
elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;
    $onu = new onuProfileController();

    switch ($oper) {
        case 'status':
            $resync = $onu->GetResyncOnu($id);
            // Creamos un array con los datos procesados
            $tel = new OpenSock($resync['OltIpPrivate']);
            $r = $tel->getStatusTel($resync);
            $tel->close();
            function extraerRx($text) {
                for ($i=0; $i <count($text) ; $i++) { 
                
                    if ($i == 2 || $i == 3) {
                        if (preg_match('/Rx\s*:\s*(-?\d+(\.\d+)?)/', $text[$i], $coincidencias)) {
                            $d[]= floatval($coincidencias[1]);
                        }else {
                            $d[] = 'N/A';
                        }
                    }
                }
                return $d;
            }
            $pote = extraerRx($r);
            $po = "ONU/OLT Rx signal {$pote[1]} dBm / {$pote[0]} dBm";
            function reFor(array $lineas): array {
                $inicioBloque = -1;
                $finBloque = -1;
            
                // Buscar el inicio (línea de guiones) y el fin (línea que inicia con "ONU interface")
                foreach ($lineas as $i => $linea) {
                    if ($inicioBloque === -1 && preg_match('/^-{5,}$/', trim($linea))) {
                        $inicioBloque = $i;
                    } elseif ($inicioBloque !== -1 && stripos($linea, 'ONU interface') === 0) {
                        $finBloque = $i;
                        break;
                    }
                }
            
                // Si no se encontraron ambos, retornamos el array original
                if ($inicioBloque === -1 || $finBloque === -1) {
                    return $lineas;
                }
            
                // Recorremos las líneas entre esos dos puntos
                for ($j = $inicioBloque + 1; $j < $finBloque; $j++) {
                    $linea = $lineas[$j];
            
                    // Buscar una MAC en los primeros 14 caracteres con formato "xxxx.xxxx.xxxx"
                    if (preg_match('/^([0-9a-fA-F]{4})\.([0-9a-fA-F]{4})\.([0-9a-fA-F]{4})/', $linea, $matches)) {
                        $macPlano = $matches[1] . $matches[2] . $matches[3];
                        // Insertar ":" cada 2 caracteres
                        $macFormateada = implode(':', str_split($macPlano, 2));
            
                        // Reemplazar la MAC original por la formateada en la línea
                        $lineas[$j] = preg_replace('/^([0-9a-fA-F]{4}\.[0-9a-fA-F]{4}\.[0-9a-fA-F]{4})/', $macFormateada, $linea);
                    }
                }
            
                return $lineas;
            }
            $r = reFor($r);
            array_unshift($r, $po);
            $text = [
                'status' => true,
                'onu' => $r
            ];
            break;
        case 'config':
            $resync = $onu->GetResyncOnu($id);
            // Creamos un array con los datos procesados
            $tel = new OpenSock($resync['OltIpPrivate']);
            $text = $tel->getConfigTel($resync);
            $tel->close();
            break;
        case 'onu':
            $r = $onu->GetResyncOnu($id);
            $text = [
                'status' => true,
                'message' => $r
            ];
            break;
        case 'onuProfile':
            $vlanP =new vlanProfileController();
            $o = $onu->getOnuProfileTable($id);
            $vlans=$vlanP->getVlanProfileTable($id);
            $text = [
                'status' => true,
                'onu' => $o,
                'vlan'=> $vlans
            ];
            break;
        case 'ipMac':
            $resync = $onu->GetResyncOnu($id);
            $mac=new ipMac();
            $tel = new OpenSock($resync['OltIpPrivate']);
            $r = $tel->getMacOnu($resync);
            $tel->close();
            $r = $mac->ip($r,$resync['OntOlt']);
            $text = [
                'status' => true,
                'ip' => $r
            ];
            break;
        default:
            $text = [
                'status' => false,
                'message' => 'exito'
            ];
            break;
    }
    
    echo json_encode($text);
}
else {
    // Optional: Method Not Allowed
    http_response_code(405);
    $text = [ 'status' => false, 'message' => 'Método no permitido' ];
}

ob_end_clean();

    // 7) Return your JSON payload:
    echo json_encode($text);
    exit;

} catch (Throwable $e) {
    // 8) On exception, drop buffer, log & return a safe JSON error
    ob_end_clean();
    error_log('onuProfile.php Exception: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'status'  => false,
        'message' => 'Error interno al procesar la solicitud'
    ]);
    exit;
}







