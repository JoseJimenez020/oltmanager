<?php
require_once(__DIR__ . '../../app/metodos/snmp/oltTemp.php');
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
$olts = new oltProfileController();
$olt = $olts->GetOlt();

$data = [];
foreach ($olt as $o) {
    $get = new oltTempS($o['OltName'], 'read');
    $temp = $get->getOltTemp();
    $get->close();
    if (array_key_exists('error', $temp)) {
        $data[] = [
            'oltId' => "{$o['IdOltList']}",
            'nameOLT' => "{$o['OltName']}",
            'time' => "{error}:{error}",
            'temp' => 'error',
            'dias' => 'error',
        ];
    } else {
        list($dias, $horas, $minutos, $segundos) = explode(':', $temp['.1.3.6.1.2.1.1.3.0']);
        $data[] = [
            'nameOLT' => $temp['.1.3.6.1.2.1.1.5.0'],
            'time' => "{$horas}:{$minutos}",
            'temp' => $temp['1.3.6.1.4.1.3902.1015.2.1.3.2.0'],
            'dias' => $dias,
        ];
    }
}

header('Content-Type: application/json');
echo json_encode($data);
