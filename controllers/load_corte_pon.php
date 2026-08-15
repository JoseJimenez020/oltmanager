<?php
/**
 * controllers/load_corte_pon.php
 *
 * Arma el JSON completo para el panel "Corte PON" de views/index.php:
 * las 5 secciones (Variaciones de señal, LOS parcial, LOS total del PON,
 * Fallo de energía, Offline N/A) en una sola respuesta.
 *
 * Fuentes de datos:
 *   - Variaciones de señal: historial_potencia (ventana de 6h, requiere
 *     que app/metodos/onuProfile/profileOnu.php::formatOnu() siga
 *     escribiendo ahí en cada ciclo de refresh_db).
 *   - Las otras 4 secciones: outage_events / outage_event_onus,
 *     mantenidas por cron/tasks/outage_events_task.php.
 *
 * IMPORTANTE - UMBRALES DE CLASIFICACIÓN (Variaciones de señal):
 * Estas constantes son una PRIMERA APROXIMACIÓN pendiente de validar
 * contra el criterio real del negocio. Ajustar libremente sin tocar el
 * resto del archivo.
 */

require_once(__DIR__ . '/../db/conn.php');

// ---- Umbrales ajustables (Variaciones de señal) ----------------------
const UMBRAL_EVENTO_DB = 1.0;   // dB de diferencia entre lecturas consecutivas para contar como "salto"
const UMBRAL_CRITICO_RX = -32.0; // dBm: RX actual en o por debajo de esto -> Crítico
const UMBRAL_ADVERTENCIA_RX = -30.0; // dBm: RX actual en o por debajo de esto (y sobre el crítico) -> Advertencia
const UMBRAL_VAR_CRITICO_DB = 5.0;   // dB: variación absoluta igual o mayor -> Crítico, sin importar el nivel
const UMBRAL_VAR_ADVERTENCIA = 2.0;   // dB: variación absoluta igual o mayor -> al menos Advertencia
const UMBRAL_EVENTOS_INESTABLE = 3;    // eventos en 6h para calificar como Inestable (si no calificó Crítico)
const VENTANA_HORAS = 6;

date_default_timezone_set('America/Merida');

$pdo = (new DbConn())->getPdo();

$respuesta = [
    'variaciones_senal' => obtenerVariacionesSenal($pdo),
    'los_parcial' => obtenerOutageGrupo($pdo, 'los', 'parcial'),
    'los_total' => obtenerOutageGrupo($pdo, 'los', 'total'),
    'fallo_energia' => obtenerOutageGrupo($pdo, 'pwrfail', null),
    'offline' => obtenerOutageGrupo($pdo, 'offline', null),
];

header('Content-Type: application/json; charset=utf-8');
echo json_encode($respuesta);


// =========================================================================
// SECCIÓN 1: Variaciones de señal
// =========================================================================


function obtenerVariacionesSenal(PDO $pdo): array
{
    $sql = "SELECT OntId, OnuSn, OltIdApi, OltName, IndexCard, IndexPort,
                   RxPrevio, RxActual, VarDb, Eventos, Severidad, UltimaFecha
            FROM variaciones_senal_cache
            ORDER BY OltIdApi, IndexCard, IndexPort, OntId";

    $stmt = $pdo->query($sql);
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $grupos = [];
    foreach ($filas as $fila) {
        $clave = $fila['OltIdApi'] . '|' . $fila['IndexCard'] . '|' . $fila['IndexPort'];

        if (!isset($grupos[$clave])) {
            $grupos[$clave] = [
                'olt_id' => (int) $fila['OltIdApi'],
                'olt_name' => $fila['OltName'],
                'tarjeta' => (int) $fila['IndexCard'],
                'puerto' => (int) $fila['IndexPort'],
                'ultimo_escaneo_raw' => $fila['UltimaFecha'],
                'onus' => [],
            ];
        }

        $var = (float) $fila['VarDb'];

        $grupos[$clave]['onus'][] = [
            'interfaz' => "gpon-onu_1/{$fila['IndexCard']}/{$fila['IndexPort']}:{$fila['OntId']}",
            'serial' => $fila['OnuSn'],
            'onu_id' => (int) $fila['OntId'],
            'previous' => round(((int) $fila['RxPrevio']) / 1000, 1),
            'current' => round(((int) $fila['RxActual']) / 1000, 1),
            'var' => $var,
            'eventos_6h' => (int) $fila['Eventos'],
            'severidad' => $fila['Severidad'],
        ];

        if ($fila['UltimaFecha'] > $grupos[$clave]['ultimo_escaneo_raw']) {
            $grupos[$clave]['ultimo_escaneo_raw'] = $fila['UltimaFecha'];
        }
    }

    $resumen = ['total_pons' => 0, 'criticos' => 0, 'advertencias' => 0, 'inestables' => 0];
    $listaGrupos = [];

    foreach ($grupos as $grupo) {
        $vars = array_map(fn($o) => abs($o['var']), $grupo['onus']);
        $eventosTotal = array_sum(array_column($grupo['onus'], 'eventos_6h'));
        $severidadPon = severidadMasAlta(array_column($grupo['onus'], 'severidad'));

        $listaGrupos[] = [
            'olt_id' => $grupo['olt_id'],
            'olt_name' => $grupo['olt_name'],
            'tarjeta' => $grupo['tarjeta'],
            'puerto' => $grupo['puerto'],
            'severidad' => $severidadPon,
            'avg_var' => round(array_sum($vars) / max(count($vars), 1), 1),
            'max_var' => round(max($vars), 1),
            'eventos_6h' => $eventosTotal,
            'ultimo_escaneo' => tiempoTranscurrido($grupo['ultimo_escaneo_raw']),
            'onus' => $grupo['onus'],
        ];

        $resumen['total_pons']++;
        if ($severidadPon === 'critico')
            $resumen['criticos']++;
        if ($severidadPon === 'advertencia')
            $resumen['advertencias']++;
        if ($severidadPon === 'inestable')
            $resumen['inestables']++;
    }

    $ordenSeveridad = ['critico' => 0, 'advertencia' => 1, 'inestable' => 2];
    usort($listaGrupos, fn($a, $b) => $ordenSeveridad[$a['severidad']] <=> $ordenSeveridad[$b['severidad']]);

    return ['resumen' => $resumen, 'grupos' => $listaGrupos];
}

function clasificarSeveridad(float $rxActual, float $var, int $eventos): string
{
    $varAbs = abs($var);

    if ($rxActual <= UMBRAL_CRITICO_RX || $varAbs >= UMBRAL_VAR_CRITICO_DB) {
        return 'critico';
    }
    if ($eventos >= UMBRAL_EVENTOS_INESTABLE) {
        return 'inestable';
    }
    if ($rxActual <= UMBRAL_ADVERTENCIA_RX || $varAbs >= UMBRAL_VAR_ADVERTENCIA) {
        return 'advertencia';
    }
    // No debería llegar aquí dado el filtro "eventos >= 1" previo, pero
    // por seguridad se clasifica como advertencia si no calificó nada más.
    return 'advertencia';
}

function severidadMasAlta(array $severidades): string
{
    if (in_array('critico', $severidades, true))
        return 'critico';
    if (in_array('inestable', $severidades, true))
        return 'inestable';
    return 'advertencia';
}


// =========================================================================
// SECCIONES 2-5: LOS parcial / LOS total / Fallo de energía / Offline
// (todas reutilizan la misma forma: agrupado por OLT, con drill-down a
// Tarjeta/Puerto)
// =========================================================================

function obtenerOutageGrupo(PDO $pdo, string $categoria, ?string $tipoAlcance): array
{
    $sql = "
        SELECT oe.OltIdApi, ol.OltName, oe.IndexCard, oe.IndexPort,
               oe.OnusAfectadas, oe.OnusTotal, oe.FechaInicio
        FROM outage_events oe
        INNER JOIN olts_list ol ON ol.OltIdApi = oe.OltIdApi
        WHERE oe.Categoria = :categoria
          AND oe.EsAbierto = 1
    ";
    $params = [':categoria' => $categoria];

    if ($tipoAlcance !== null) {
        $sql .= " AND oe.TipoAlcance = :tipo";
        $params[':tipo'] = $tipoAlcance;
    }

    $sql .= " ORDER BY ol.OltName, oe.IndexCard, oe.IndexPort";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $filas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $porOlt = [];
    foreach ($filas as $fila) {
        $oltId = (int) $fila['OltIdApi'];
        if (!isset($porOlt[$oltId])) {
            $porOlt[$oltId] = [
                'olt_id' => $oltId,
                'olt_name' => $fila['OltName'],
                'total_pons' => 0,
                'total_onus' => 0,
                'desde_raw' => $fila['FechaInicio'], // se queda con la más antigua
                'puertos' => [],
            ];
        }

        $onusAfectadas = (int) $fila['OnusAfectadas'];
        $onusTotal = (int) $fila['OnusTotal'];

        $porOlt[$oltId]['total_pons']++;
        $porOlt[$oltId]['total_onus'] += $onusAfectadas;
        if ($fila['FechaInicio'] < $porOlt[$oltId]['desde_raw']) {
            $porOlt[$oltId]['desde_raw'] = $fila['FechaInicio'];
        }

        $porOlt[$oltId]['puertos'][] = [
            'tarjeta' => (int) $fila['IndexCard'],
            'puerto' => (int) $fila['IndexPort'],
            'onus_afectadas' => $onusAfectadas,
            'onus_total' => $onusTotal,
            'porcentaje' => $onusTotal > 0 ? round(($onusAfectadas / $onusTotal) * 100, 1) : 0,
            'desde' => tiempoTranscurrido($fila['FechaInicio']),
        ];
    }

    $grupos = [];
    foreach ($porOlt as $g) {
        $g['desde'] = tiempoTranscurrido($g['desde_raw']);
        unset($g['desde_raw']);
        $grupos[] = $g;
    }

    return [
        'resumen' => [
            'total_pons' => array_sum(array_column($grupos, 'total_pons')),
            'total_onus' => array_sum(array_column($grupos, 'total_onus')),
        ],
        'grupos' => $grupos,
    ];
}


// =========================================================================
// Helper compartido
// =========================================================================

function tiempoTranscurrido(?string $fecha): string
{
    if (!is_string($fecha) || $fecha === '') {
        return '';
    }
    try {
        $zona = new DateTimeZone('America/Merida');
        $ahora = new DateTime('now', $zona);
        $pasada = new DateTime($fecha, $zona);
        $diferencia = $ahora->diff($pasada);

        if ($diferencia->y > 0)
            return $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '') . ' hace';
        if ($diferencia->m > 0)
            return $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '') . ' hace';
        if ($diferencia->d >= 7) {
            $semanas = (int) floor($diferencia->d / 7);
            return $semanas . ' semana' . ($semanas > 1 ? 's' : '') . ' hace';
        }
        if ($diferencia->d > 0)
            return $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '') . ' hace';
        if ($diferencia->h > 0)
            return $diferencia->h . ' hora' . ($diferencia->h > 1 ? 's' : '') . ' hace';
        if ($diferencia->i > 0)
            return $diferencia->i . ' minuto' . ($diferencia->i > 1 ? 's' : '') . ' hace';
        return 'Hace unos segundos';
    } catch (Exception $e) {
        return '';
    }
}