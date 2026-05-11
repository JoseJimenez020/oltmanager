<?php

require_once(__DIR__ . '../../app/metodos/OnuDb.php');
require_once(__DIR__ . '../../app/metodos/onus/onu.php');
require_once(__DIR__ . '../../app/metodos/gpon/gpon.php');
$query = new OnuDb();
$Onus = new Onus();
$Gpon = new Gpon();

$sql = $query->GetOnu();
$sql_off = $query->GetOnuOff();
$sql_low = $query->GetOnuLow();