<?php
// 1) Force JSON output and silence on-screen errors
header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors',   0);
ini_set('log_errors',       1);
ini_set('error_log',        (__DIR__ . '../../logs/php_errors.log'));
error_reporting(E_ALL);

// 2) Catch any stray output
ob_start();

// 3) Your helper functions (distanciaPotencia, tiempoTranscurrido, obtenerStatus)...
function distanciaPotencia($rx, $metros) {
    if ($rx > -30000) {
        return '<span class="material-symbols-outlined succes">signal_cellular_alt</span>' . number_format($rx / 1000, 2) . 'dBm /' . " (" . $metros . "m)" ;
    } else if (($rx <= -30000) && ($rx > -32000)) {
        return '<span class="material-symbols-outlined warning">signal_cellular_alt</span>' . number_format($rx / 1000, 2) . 'dBm /' . " (" . $metros . "m)" ;
    } else if (($rx <= -32000) && ($rx > -80000)) {
        return '<span class="material-symbols-outlined danger">signal_cellular_alt</span>' . number_format($rx / 1000, 2) . 'dBm /' . " (" . $metros . "m)" ;
    } else if ($rx <= -80000) {
        return ("-");
    }
}

function tiempoTranscurrido($fecha) {
    if (!is_string($fecha)) {
        return 'Fecha no válida';
    }

    try {
        $zona = new DateTimeZone('America/Merida');
        $ahora = new DateTime('now', $zona);
        $pasada = new DateTime($fecha, $zona);
        $diferencia = $ahora->diff($pasada);

        if ($diferencia->y > 0) {
            return $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '') . ' atrás';
        } elseif ($diferencia->m > 0) {
            return $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '') . ' atrás';
        } elseif ($diferencia->d >= 7) {
            $semanas = floor($diferencia->d / 7);
            return $semanas . ' semana' . ($semanas > 1 ? 's' : '') . ' atrás';
        } elseif ($diferencia->d > 0) {
            return $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '') . ' atrás';
        } elseif ($diferencia->h > 0) {
            return $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '') . ' atrás';
        } elseif ($diferencia->i > 0) {
            return $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '') . ' atrás';
        } else {
            return 'Hace unos segundos';
        }
    } catch (Exception $e) {
        return 'Error: ' . $e->getMessage();
    }
}


function obtenerStatus($estado) {
    switch($estado) {
        case "0": return '<span class="material-symbols-outlined warning">sync_problem</span>';
        case "1": return '<span class="material-symbols-outlined">link_off</span>';
        case "2": return '<span class="material-symbols-outlined">sync</span>';
        case "3": return '<span class="material-symbols-outlined succes">public</span>';
        case "4": return '<span class="material-symbols-outlined">power_off</span>';
        case "5": return '<span class="material-symbols-outlined warning">signal_wifi_off</span>';
        case "6": return '<span class="material-symbols-outlined">public</span>';
        default: "-";
    }
}

try {
    // 4) Fix your paths: note the leading slash before ../../
    require_once (__DIR__ . '../../app/metodos/onuProfile/onuProfileController.php');
    require_once (__DIR__ . '../../app/metodos/onuProfile/getProfileOnu.php');
    require_once (__DIR__ . '../../app/metodos/snmp/oltSnmp.php');
    require_once (__DIR__ . '../../app/metodos/onus/onu.php');

    // 5) Validate input
    $id = $_GET['pass'] ?? null;
    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'Falta parámetro pass.']);
        exit;
    }

    // 6) Business logic
    $profile   = (new onuProfileController())->GetResyncOnu($id);
    $index     = $profile['IndexOid'] .'.'. $profile['OntPos'];
    $snmp      = new nSnmp($profile['OntOlt'], 'read');
    $poS       = new getProfileOnu($snmp);
    $onuData   = $poS->getProfileOnu($index);
    $fechaDb   = (new Onus())->GetTiempo($id)['Date'];

    $status    = $onuData[1];
    $distancia = $onuData[2];
    $potencia  = $onuData[0];
    $admin     = $onuData[3];
    $ip        = $onuData[4];

    $payload = [
      'status'            => obtenerStatus($status) . ' ' . tiempoTranscurrido($fechaDb),
      'distanciaPotencia' => distanciaPotencia($potencia, $distancia),
      'admin'             => $admin,
      'ip'                => $ip
    ];

    // 7) Clean any stray buffer, output our JSON, and exit
    ob_clean();
    echo json_encode($payload);
    exit;

} catch (Throwable $e) {
    // 8) On any error, return a minimal JSON error
    error_log('refresh_onu.php Exception: ' . $e->getMessage());
    http_response_code(500);
    ob_clean();
    echo json_encode([
      'error'   => 'Error interno al obtener datos ONU.',
      'details' => $e->getMessage()
    ]);
    exit;
}