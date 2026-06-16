<?php
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
require_once(__DIR__ . '../../app/metodos/logs/logsController.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $oper  = isset($_GET['accion']) ? $_GET['accion'] : null;
    $idOlt = isset($_GET['olt'])    ? $_GET['olt']    : null;
    $oltDb = new oltProfileController();

    switch ($oper) {
        case 'all':
            $olts = $oltDb->GetOlt();
            $text = ['status' => true, 'message' => $olts];
            break;
        case 'one':
            $olt  = $oltDb->GetOne($idOlt);
            $text = ['status' => true, 'message' => $olt];
            break;
        case 'listName':
            $olt  = $oltDb->getOltList();
            $text = ['status' => true, 'olt' => $olt];
            break;
        default:
            $text = ['status' => false, 'message' => 'Fallo al recibir datos'];
            break;
    }

    header('Content-Type: application/json');
    echo json_encode($text);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    $oltDb = new oltProfileController();
    $logs  = new logsController();
    $form  = json_decode(file_get_contents('php://input'), true);

    $r = $oltDb->UpdateOltProfile($form);
    $logs->insertLogs(null, $form['id'], 'informacion de olt', $_SERVER['REQUEST_METHOD'], false);

    header('Content-Type: application/json');
    echo json_encode(['status' => true, 'message' => $r]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // ── Leer accion del query string ──────────────────────────────
    $accion = isset($_GET['accion']) ? $_GET['accion'] : '';
    $body   = json_decode(file_get_contents('php://input'), true) ?? [];

    // ── 1. Test de conexión Telnet ────────────────────────────────
    if ($accion === 'testConnection') {

        $name = trim($body['olt_name']        ?? '');
        $ip   = trim($body['olt_ip']          ?? '');
        $port = (int) ($body['telnet_port']   ?? 23);
        $user = trim($body['telnet_user']      ?? '');
        $pass = trim($body['telnet_password']  ?? '');

        $missing = [];
        if (empty($name)) $missing[] = 'olt_name';
        if (empty($ip))   $missing[] = 'olt_ip';
        if (empty($user)) $missing[] = 'telnet_user';
        if (empty($pass)) $missing[] = 'telnet_password';

        if (!empty($missing)) {
            http_response_code(400);
            header('Content-Type: application/json');
            echo json_encode([
                'status'  => false,
                'message' => 'Campos faltantes: ' . implode(', ', $missing)
            ]);
            exit;
        }

        $errno  = 0;
        $errstr = '';
        set_error_handler(function ($errno, $errstr) {
            throw new \Exception($errstr);
        });
        try {
            $con = fsockopen($ip, $port ?: 23, $errno, $errstr, 5);
            if (!$con) throw new \Exception("Sin respuesta en $ip:$port — $errstr (código $errno)");
            fclose($con);
            $text = ['status' => true,  'message' => "Conexión Telnet exitosa a $ip:$port"];
        } catch (\Throwable $th) {
            $text = ['status' => false, 'message' => $th->getMessage()];
        } finally {
            restore_error_handler();
        }

        header('Content-Type: application/json');
        echo json_encode($text);
        exit;
    }

    // ── 2. Registro de nueva OLT ──────────────────────────────────
    if ($accion === 'inOnuOlt') {
        require_once(__DIR__ . '../../app/metodos/onuProfile/profileOnu.php');
        $m        = new profileOnu();
        $m->setOlt();
        $profile  = $m->getProfiles();
        $onus     = $m->insertOnus($profile);
        $m->setGponOnu();
        $potencia = $m->insertPotencia($profile);
        $vlans    = $m->getVlans();
        $vlan     = $m->insertVlans($vlans);

        header('Content-Type: application/json');
        echo json_encode(['status' => $vlan, 'entity' => '']);
        exit;
    }

    // ── 3. Registro de nueva OLT (sin accion en query string) ─────
    $required = [
        'olt_name', 'olt_ip', 'telnet_port',
        'telnet_user', 'telnet_password',
        'snmp_ro', 'snmp_rw', 'snmp_port',
        'hardware_version', 'software_version'
    ];

    foreach ($required as $field) {
        if (empty($body[$field])) {
            http_response_code(400);
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

    $existe = $oltDb->GetSnmpProfile($body['olt_name']);
    if ($existe) {
        http_response_code(409);
        header('Content-Type: application/json');
        echo json_encode([
            'status'  => false,
            'message' => 'Ya existe una OLT registrada con ese nombre.'
        ]);
        exit;
    }

    $result = $oltDb->InsertOlt($body);

    if ($result) {
        $logs->insertLogs(null, $result, 'nueva olt registrada', $_SERVER['REQUEST_METHOD'], false);
        $text = [
            'status'  => true,
            'message' => 'OLT registrada correctamente.',
            'id'      => $result
        ];
    } else {
        http_response_code(500);
        $text = [
            'status'  => false,
            'message' => 'Error al registrar la OLT en la base de datos.'
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($text);
    exit;
}