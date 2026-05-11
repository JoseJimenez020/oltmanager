<?php
include 'query_db.php';

$pon_outage = $Gpon->GetPonOutage();
// Simula los datos consultados desde la base de datos.
$data = [];

// Tu lógica para llenar $data con datos reales.
foreach ($pon_outage as $row) {
    $data[] = [
        'olt' => $row['OltName'],
        'gpon' => $row['IndexCard'] . "/" . $row['IndexPort'],
        'onus' => $row['total'],
        'los' => $row['total_los'],
        'pfail' => $row['total_pfail'],
        'offline' => $row['total_offline'],
        'caidos' => $row['total_caidos'],
        'mensaje' => ($row['total_los'] > $row['total_pfail']) ? "Corte de fibra" : (($row['total_los'] < $row['total_pfail']) ? "Falla de energia" : "Deshabilitado"),
        'desde' => $row['ultima_caida'],
        'hace' => tiempoTranscurrido($row['ultima_caida'])
    ];
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

// Retorna los datos como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>