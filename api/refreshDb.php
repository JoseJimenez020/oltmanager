<?php
require_once(__DIR__ . '..\..\app\metodos\onuProfile\profileOnu.php');
require_once(__DIR__ . '..\..\app\metodos\potencia\statusOnu.php');
require_once(__DIR__ . '..\..\app\metodos\migracion\migracionStatus.php');
require_once (__DIR__ . '..\..\app\metodos\bandWith\bandWithController.php');


$band= new bandWithController();

try {
    $m= new profileOnu();
    $m->setOlt();

    $profile = $m->getProfiles();
    $m->setGponOnu();
    $update = $m->formatOnu($profile);
    $s= new statusOnu();
    $status = $s->updateStatus($update['existe']);

    $onus = $m->insertOnus($update['nuevo']);
    $m->unsetGponOnu();
    $m->setGponOnu();
    $potencia = $m->insertPotencia($update['nuevo']);
    $vlans = $m->getVlans();
    $new = $m->newVlans($update,$vlans);
    $vlan = $m->insertVlans($new);
    $m = new migracionStatus();
    $migracion = $m->insertMigracion($update['migracion']);
    sleep(1);
    $band->insertBand();
    $text = [
        'status' => true,
        'message' => "Actualiza: $status Inserta: $onus Potencia: $potencia Vlan: $vlan Migracion: $migracion"
    ];
    header('Content-Type: application/json');
    echo json_encode($text);
} catch (Exception $e) {
    
    $text = [
        'status' => false,
        'message' => $e->getMessage()
    ]; 
    header('Content-Type: application/json');
    echo json_encode($text);
}