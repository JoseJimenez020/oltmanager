<?php
require_once(__DIR__ . '../../app/metodos/snmp/oltTemp.php');
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
$olts = new oltProfileController();
$olt = $olts->GetOlt();

$data = [];
foreach ($olt as $o) {
    $get = new oltTempS($o['OltName'], 'read');
    $temp = $get->getOltTemp();

    if (!is_array($temp) || array_key_exists('error', $temp)) {
        $data[] = [
            'oltId'   => (string) $o['OltIdApi'],
            'nameOLT' => (string) $o['OltName'],
            'time'    => '--:--',
            'temp'    => 'error',
            'dias'    => 'error',
        ];
        continue;
    }

    $uptime = $temp['.1.3.6.1.2.1.1.3.0'] ?? '';
    $partes = explode(':', $uptime);
    [$dias, $horas, $minutos] = array_pad($partes, 3, '0');

    $data[] = [
        'nameOLT' => $temp['.1.3.6.1.2.1.1.5.0'] ?? $o['OltName'],
        'time'    => "{$horas}:{$minutos}",
        'temp'    => $temp['1.3.6.1.4.1.3902.1015.2.1.3.2.0'] ?? 'N/A',
        'dias'    => $dias,
    ];
}

header('Content-Type: application/json');
echo json_encode($data);