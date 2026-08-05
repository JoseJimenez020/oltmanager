<?php
/**
 * cron/tasks/outage_events_task.php
 *
 * Detecta y mantiene el estado de outage_events / outage_event_onus por
 * puerto GPON (OltIdApi + IndexCard + IndexPort).
 *
 * VERSION 4: la v3 ya agrupaba escrituras en lotes, pero seguía haciendo
 * el JOIN completo onu+gpon+potencia UNA VEZ POR CADA CATEGORIA (3 veces
 * en total), cuando es exactamente el mismo JOIN con distinto Status a
 * contar. Con potencia teniendo cientos de miles de filas, eso significaba
 * re-escanear el mismo dataset 3 veces (625s medidos en producción pese al
 * batching de v3). Esta versión hace UNA sola pasada de lectura que
 * calcula las 3 categorías a la vez, y agrupa también las escrituras de
 * las 3 categorías donde es posible (misma tabla destino).
 *
 * Debe ejecutarse DESPUÉS de refresh_db_task.php en el mismo ciclo.
 *
 * Mapeo de categorías -> Status:
 *   los      -> Status = 1
 *   pwrfail  -> Status = 4
 *   offline  -> Status = 6
 */

require_once(__DIR__ . '/../../db/conn.php');

date_default_timezone_set('America/Merida');

$pdo = (new DbConn())->getPdo();

const OUTAGE_CATEGORIAS = [
    'los'     => 1,
    'pwrfail' => 4,
    'offline' => 6,
];

$fecha = date('Y-m-d H:i:s');

function outageEventsTask_clave($olt, $card, $port): string
{
    return "$olt|$card|$port";
}

function outageEventsTask_insertarEnLotes(PDO $pdo, string $sqlTemplate, string $rowPlaceholder, array $filas, int $chunkSize): void
{
    foreach (array_chunk($filas, $chunkSize) as $chunk) {
        $placeholders = implode(',', array_fill(0, count($chunk), $rowPlaceholder));
        $params = [];
        foreach ($chunk as $fila) {
            foreach ($fila as $valor) {
                $params[] = $valor;
            }
        }
        $pdo->prepare(sprintf($sqlTemplate, $placeholders))->execute($params);
    }
}

// =====================================================================
// PASO 1: Estado agregado por puerto para LAS 3 CATEGORIAS A LA VEZ.
// Antes: 1 query completa por categoría (3 escaneos del mismo JOIN).
// Ahora: 1 sola query total.
// =====================================================================
$stmt = $pdo->query(
    "SELECT o.OntOlt AS OltIdApi, g.IndexCard, g.IndexPort,
            COUNT(*) AS Total,
            SUM(CASE WHEN p.Status = 1 THEN 1 ELSE 0 END) AS Los,
            SUM(CASE WHEN p.Status = 4 THEN 1 ELSE 0 END) AS Pwrfail,
            SUM(CASE WHEN p.Status = 6 THEN 1 ELSE 0 END) AS Offline
     FROM onu o
     INNER JOIN gpon g ON g.IdOlt = o.OntGpon
     INNER JOIN potencia p ON p.Onu = o.OntId
     GROUP BY o.OntOlt, g.IndexCard, g.IndexPort"
);
$puertos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// =====================================================================
// PASO 2: TODAS las ONUs en estado los/pwrfail/offline, en una sola
// consulta (antes: 1 por categoría). Se agrupan en memoria por
// [status][puerto] => [OntId,...].
// =====================================================================
$stmtOnusTodas = $pdo->query(
    "SELECT o.OntOlt AS OltIdApi, g.IndexCard, g.IndexPort, o.OntId, p.Status
     FROM onu o
     INNER JOIN gpon g ON g.IdOlt = o.OntGpon
     INNER JOIN potencia p ON p.Onu = o.OntId
     WHERE p.Status IN (1, 4, 6)"
);
$onusPorStatusPuerto = []; // [status][key] => [OntId,...]
foreach ($stmtOnusTodas->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = outageEventsTask_clave($row['OltIdApi'], $row['IndexCard'], $row['IndexPort']);
    $onusPorStatusPuerto[(int) $row['Status']][$key][] = (int) $row['OntId'];
}

// =====================================================================
// PASO 3: TODOS los eventos abiertos de las 3 categorías en una sola
// consulta (antes: 1 por categoría).
// =====================================================================
$stmtAbiertos = $pdo->query(
    "SELECT IdEvent, OltIdApi, IndexCard, IndexPort, Categoria
     FROM outage_events WHERE EsAbierto = 1"
);
$abiertosPorCategoriaPuerto = []; // [categoria][key] => IdEvent
foreach ($stmtAbiertos->fetchAll(PDO::FETCH_ASSOC) as $row) {
    $key = outageEventsTask_clave($row['OltIdApi'], $row['IndexCard'], $row['IndexPort']);
    $abiertosPorCategoriaPuerto[$row['Categoria']][$key] = (int) $row['IdEvent'];
}

// =====================================================================
// PASO 4: clasificar en memoria (sin tocar la DB) para las 3 categorías.
// =====================================================================
$columnaPorCategoria = ['los' => 'Los', 'pwrfail' => 'Pwrfail', 'offline' => 'Offline'];

$paraCrearActualizar = []; // filas a upsert, de las 3 categorías juntas
$idsParaCerrar = [];       // ids a cerrar, de las 3 categorías juntas
$resumenPorCategoria = ['los' => ['abiertos' => 0, 'cerrados' => 0], 'pwrfail' => ['abiertos' => 0, 'cerrados' => 0], 'offline' => ['abiertos' => 0, 'cerrados' => 0]];

foreach ($puertos as $puerto) {
    $olt   = (int) $puerto['OltIdApi'];
    $card  = (int) $puerto['IndexCard'];
    $port  = (int) $puerto['IndexPort'];
    $total = (int) $puerto['Total'];
    $key = outageEventsTask_clave($olt, $card, $port);

    foreach (OUTAGE_CATEGORIAS as $categoria => $statusCode) {
        $afectadas = (int) $puerto[$columnaPorCategoria[$categoria]];
        $idAbierto = $abiertosPorCategoriaPuerto[$categoria][$key] ?? null;

        if ($afectadas === 0) {
            if ($idAbierto) {
                $idsParaCerrar[] = $idAbierto;
                $resumenPorCategoria[$categoria]['cerrados']++;
            }
            continue;
        }

        $tipoAlcance = ($afectadas === $total) ? 'total' : 'parcial';
        $paraCrearActualizar[] = [$olt, $card, $port, $categoria, $tipoAlcance, $afectadas, $total, $fecha, $fecha];
        $resumenPorCategoria[$categoria]['abiertos']++;
    }
}

// =====================================================================
// PASO 5: cerrar eventos resueltos de las 3 categorías en un solo
// UPDATE...IN(...) y un solo DELETE...IN(...) para su detalle.
// =====================================================================
if ($idsParaCerrar) {
    $placeholders = implode(',', array_fill(0, count($idsParaCerrar), '?'));
    $pdo->prepare("UPDATE outage_events SET FechaFin = ?, UltimaActualizacion = ? WHERE IdEvent IN ($placeholders)")
        ->execute(array_merge([$fecha, $fecha], $idsParaCerrar));
    $pdo->prepare("DELETE FROM outage_event_onus WHERE IdEvent IN ($placeholders)")
        ->execute($idsParaCerrar);
}

// =====================================================================
// PASO 6: crear/actualizar en lotes multi-fila (mismo mecanismo que v3),
// pero ahora con filas de las 3 categorías mezcladas en los mismos lotes
// ya que van a la misma tabla destino.
// =====================================================================
if ($paraCrearActualizar) {
    $sqlUpsertTemplate =
        "INSERT INTO outage_events
            (OltIdApi, IndexCard, IndexPort, Categoria, TipoAlcance,
             OnusAfectadas, OnusTotal, FechaInicio, UltimaActualizacion)
         VALUES %s AS nueva
         ON DUPLICATE KEY UPDATE
            TipoAlcance = nueva.TipoAlcance,
            OnusAfectadas = nueva.OnusAfectadas,
            OnusTotal = nueva.OnusTotal,
            UltimaActualizacion = nueva.UltimaActualizacion";
    outageEventsTask_insertarEnLotes($pdo, $sqlUpsertTemplate, '(?,?,?,?,?,?,?,?,?)', $paraCrearActualizar, 200);

    // Releer TODOS los eventos abiertos frescos (1 sola query para las 3
    // categorías, ya con los recién creados incluidos).
    $stmtAbiertosFresco = $pdo->query(
        "SELECT IdEvent, OltIdApi, IndexCard, IndexPort, Categoria
         FROM outage_events WHERE EsAbierto = 1"
    );
    $abiertosFrescoPorCategoriaPuerto = [];
    foreach ($stmtAbiertosFresco->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = outageEventsTask_clave($row['OltIdApi'], $row['IndexCard'], $row['IndexPort']);
        $abiertosFrescoPorCategoriaPuerto[$row['Categoria']][$key] = (int) $row['IdEvent'];
    }

    $idsAbiertosFrescos = [];
    foreach ($abiertosFrescoPorCategoriaPuerto as $mapa) {
        foreach ($mapa as $idEvent) {
            $idsAbiertosFrescos[] = $idEvent;
        }
    }

    // Borrar TODO el detalle previo de los eventos que siguen abiertos,
    // en un solo DELETE...IN(...) para las 3 categorías juntas.
    if ($idsAbiertosFrescos) {
        $placeholders = implode(',', array_fill(0, count($idsAbiertosFrescos), '?'));
        $pdo->prepare("DELETE FROM outage_event_onus WHERE IdEvent IN ($placeholders)")
            ->execute($idsAbiertosFrescos);
    }

    // Insertar TODO el detalle nuevo en lotes multi-fila, de las 3
    // categorías juntas.
    $filasDetalle = [];
    foreach ($paraCrearActualizar as $fila) {
        [$olt, $card, $port, $categoria] = $fila;
        $key = outageEventsTask_clave($olt, $card, $port);
        $idEvent = $abiertosFrescoPorCategoriaPuerto[$categoria][$key] ?? null;
        if (!$idEvent) continue;
        $statusCode = OUTAGE_CATEGORIAS[$categoria];
        foreach ($onusPorStatusPuerto[$statusCode][$key] ?? [] as $ontId) {
            $filasDetalle[] = [$idEvent, $ontId, $fecha];
        }
    }
    if ($filasDetalle) {
        $sqlDetalleTemplate = "INSERT IGNORE INTO outage_event_onus (IdEvent, OntId, FechaDetectado) VALUES %s";
        outageEventsTask_insertarEnLotes($pdo, $sqlDetalleTemplate, '(?,?,?)', $filasDetalle, 500);
    }
}

return implode(' | ', array_map(
    fn($cat, $r) => "$cat: {$r['abiertos']} abiertos, {$r['cerrados']} cerrados",
    array_keys($resumenPorCategoria),
    $resumenPorCategoria
));
