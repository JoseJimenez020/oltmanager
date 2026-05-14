<?php
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '../../app/metodos/logs/logsController.php');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;
    $idOlt = isset($_GET['olt']) ? $_GET['olt'] : null;
    $oltDb = new oltProfileController();

    switch ($oper) {

        case 'all':
            $olts = $oltDb->GetOlt();
            $text = [
                'status' => true,
                'message' => $olts
            ];
            break;
        case 'one':
            $olt = $oltDb->GetOne($idOlt);
            $text = [
                'status' => true,
                'message' => $olt
            ];
            break;
        case 'listName':
            $olt = $oltDb->getOltList();
            $text = [
                'status' => true,
                'olt' => $olt
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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $oper = json_decode(file_get_contents('php://input'), true);

    // Validar que vengan los campos mínimos obligatorios
    $required = [
        'olt_name',
        'olt_ip',
        'telnet_port',
        'telnet_user',
        'telnet_password',
        'snmp_ro',
        'snmp_rw',
        'snmp_port',
        'hardware_version',
        'software_version'
    ];

    foreach ($required as $field) {
        if (empty($oper[$field])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            echo json_encode([
                'status' => false,
                'message' => "Campo requerido faltante: $field"
            ]);
            exit;
        }
    }

    $oltDb = new oltProfileController();
    $logs = new logsController();

    // Verificar si ya existe una OLT con ese nombre o IP
    $existe = $oltDb->GetSnmpProfile($oper['olt_name']);
    if ($existe) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 409 Conflict');
        echo json_encode([
            'status' => false,
            'message' => 'Ya existe una OLT con ese nombre.'
        ]);
        exit;
    }

    $result = $oltDb->InsertOlt($oper);

    if ($result) {
        $logs->insertLogs(null, $result, 'nueva olt registrada', $_SERVER['REQUEST_METHOD'], false);
        $text = [
            'status' => true,
            'message' => 'OLT registrada correctamente.',
            'id' => $result   // OltIdApi asignado
        ];
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        $text = [
            'status' => false,
            'message' => 'Error al registrar la OLT en la base de datos.'
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($text);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $oltDb = new oltProfileController();
    $logs = new logsController();
    $form = json_decode(file_get_contents('php://input'), true);

    $r = $oltDb->UpdateOltProfile($form);
    $logs->insertLogs(null, $form['id'], "informacion de olt", $_SERVER['REQUEST_METHOD'], false);
    $text = [
        'status' => true,
        'message' => $r
    ];
    header('Content-Type: application/json');
    echo json_encode($text);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── 1. Test de conexión Telnet ────────────────────────────────
    // Se resuelve ANTES de leer/validar el body de registro,
    // porque viene con ?accion=testConnection en la URL.
    if (isset($_GET['accion']) && $_GET['accion'] === 'testConnection') {

        $body = json_decode(file_get_contents('php://input'), true);

        $ip   = trim($body['olt_ip']          ?? '');
        $port = (int)($body['telnet_port']     ?? 23);
        $user = trim($body['telnet_user']      ?? '');
        $pass = trim($body['telnet_password']  ?? '');

        if (empty($ip) || empty($user) || empty($pass)) {
            header('Content-Type: application/json');
            echo json_encode(['status' => false, 'message' => 'Completa IP, usuario y contraseña.']);
            exit;
        }

        $errno  = 0;
        $errstr = '';
        $con    = @fsockopen($ip, $port, $errno, $errstr, 3);

        if ($con) {
            fclose($con);
            $text = ['status' => true,  'message' => "Conexión Telnet exitosa a $ip:$port"];
        } else {
            $text = ['status' => false, 'message' => "No se pudo conectar a $ip:$port — $errstr (código $errno)"];
        }

        header('Content-Type: application/json');
        echo json_encode($text);
        exit;
    }

    // ── 2. Registro de nueva OLT ──────────────────────────────────
    $oper = json_decode(file_get_contents('php://input'), true);

    $required = [
        'olt_name', 'olt_ip', 'telnet_port',
        'telnet_user', 'telnet_password',
        'snmp_ro', 'snmp_rw', 'snmp_port',
        'hardware_version', 'software_version'
    ];

    foreach ($required as $field) {
        if (empty($oper[$field])) {
            header($_SERVER['SERVER_PROTOCOL'] . ' 400 Bad Request');
            header('Content-Type: application/json');
            echo json_encode([
                'status'  => false,
                'message' => "Campo requerido faltante: $field"
            ]);
            exit;
        }
    }

    $oltDb = new oltProfileController();
    $logs  = new logsController();

    // Verificar duplicado por nombre
    $existe = $oltDb->GetSnmpProfile($oper['olt_name']);
    if ($existe) {
        header($_SERVER['SERVER_PROTOCOL'] . ' 409 Conflict');
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => false,
            'message' => 'Ya existe una OLT registrada con ese nombre.'
        ]);
        exit;
    }

    $result = $oltDb->InsertOlt($oper);

    if ($result) {
        $logs->insertLogs(null, $result, 'nueva olt registrada', $_SERVER['REQUEST_METHOD'], false);
        $text = [
            'status'  => true,
            'message' => 'OLT registrada correctamente.',
            'id'      => $result
        ];
    } else {
        header($_SERVER['SERVER_PROTOCOL'] . ' 500 Internal Server Error');
        $text = [
            'status'  => false,
            'message' => 'Error al registrar la OLT en la base de datos.'
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($text);
    exit;
}
