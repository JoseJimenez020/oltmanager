<?php
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '../../app/metodos/vlanProfile/vlanProfileController.php');
require_once(__DIR__ . '../../app/metodos/speedProfile/speedProfileController.php');
require_once(__DIR__ . '../../app/metodos/onuType/onuTypeController.php');

$o=new oltProfileController();
$vlanP =new vlanProfileController();
$speedP =new speedProfileController();
$typeP = new onuTypeController();

if (!empty($_GET['tarjeta'])) {
    $tarjeta = $_GET['tarjeta'];
    $puerto = $_GET['puerto'];
    $serie = $_GET['serie'];
    $tipoGet = $_GET['tipo'];
    $idOlt = $_GET['olt'];
    $zona=$o->GetOne($idOlt);
    
    $type=$typeP->GetOnuType();
    $vlanPerOlt=$vlanP->GetVlanForm($idOlt);
    $download=$speedP->GetSpeedTypeProfile('down',$idOlt);
    $upload=$speedP->GetSpeedTypeProfile('up',$idOlt);
}