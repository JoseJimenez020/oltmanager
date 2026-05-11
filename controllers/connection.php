<?php
require_once(__DIR__ . '../../app/metodos/OnuGet.php');
$snmpGet=new OnuGet();
$pass= isset($_GET['pass']) ? $_GET['pass'] : 'central';;
$name = $snmpGet->DataArray($pass, 'OnuName');
$model = $snmpGet->DataArray($pass, 'OnuModel');
$desc = $snmpGet->DataArray($pass, 'OnuDesc');
$status = $snmpGet->DataArray($pass, 'OnuStatus');
$rx = $snmpGet->DataArray($pass, 'OnuRxOlt');
$distance = $snmpGet->DataArray($pass, 'OnuDistance');

//function obtenerZona($cadena) {
//    // Primero eliminamos el prefijo "zone_" y el sufijo que comienza con "_authd" o "_descr_"
//    $cadena = preg_replace('/^zone_/', '', $cadena);  // Elimina el prefijo "zone_"
//    $cadena = preg_replace('/(_authd|_descr_).*$/', '', $cadena);  // Elimina el sufijo
//
//    // Reemplaza los guiones bajos por espacios
//    $cadena = str_replace('_', ' ', $cadena);
//
//    // Devuelve la cadena resultante
//    return $cadena;
//}

$index = $snmpGet->IndexArray($pass);
$nameOLT=$snmpGet->GetSysName();    
$time=$snmpGet->GetTimeUp();
$temp=$snmpGet->GetTemp();


?>