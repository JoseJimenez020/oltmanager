<?php
/**
 * cron/tasks/outage_events_task.php
 *
 * Detecta y mantiene el estado de outage_events / outage_event_onus por
 * puerto GPON (OltIdApi + IndexCard + IndexPort).
 *
 * VERSION 3 (batching agresivo): la v2 seguía haciendo 1 UPDATE/INSERT por
 * puerto individual (~900 puertos afectados => ~2700+ round-trips => 474s).
 * Esta versión usa INSERT ... ON DUPLICATE KEY UPDATE multi-fila (apoyado
 * en el UNIQUE KEY sobre EsAbierto: solo puede existir un evento abierto
 * por puerto+categoria a la vez, así que el propio motor de MySQL decide
 * "crear nuevo" vs "actualizar el abierto" sin que PHP tenga que
 * preguntarlo antes). Los cierres y el detalle por ONU también se agrupan
 * en consultas IN(...) en vez de una por puerto.
 *
 * Round-trips por categoría: ~10-15 (antes: ~3 por puerto, miles en total).
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

$categorias = [
    'los'     => 1,
    'pwrfail' => 4,
    'offline' => 6,
];

$resumenGlobal = [];
foreach ($categorias as $categoria => $statusCode) {
    $resumenGlobal[$categoria] = outageEventsTask_procesarCategoria($pdo, $categoria, $statusCode);
}

return implode(' | ', array_map(
    fn($cat, $r) => "$cat: {$r['abiertos']} abiertos, {$r['cerrados']} cerrados",
    array_keys($resumenGlobal),
    $resumenGlobal
));

function outageEventsTask_clave($olt, $card, $port): string
{
    return "$olt|$card|$port";
}

/**
 * Divide un array de filas en chunks e inserta cada chunk como un solo
 * INSERT multi-fila. Evita exceder max_allowed_packet / límites de
 * parámetros con volúmenes grandes.
 */
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

function outageEventsTask_procesarCategoria(PDO $pdo, string $categoria, int $statusCode): array
{
    $fecha = date('Y-m-d H:i:s');

    // 1) Estado agregado por puerto (1 query)
    $stmt = $pdo->prepare(
        "SELECT o.OntOlt AS OltIdApi, g.IndexCard, g.IndexPort,
                COUNT(*) AS Total,
                SUM(CASE WHEN p.Status = :statusCode THEN 1 ELSE 0 END) AS Afectadas
         FROM onu o
         INNER JOIN gpon g ON g.IdOlt = o.OntGpon
         INNER JOIN potencia p ON p.Onu = o.OntId
         GROUP BY o.OntOlt, g.IndexCard, g.IndexPort"
    );
    $stmt->execute([':statusCode' => $statusCode]);
    $puertos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 2) TODAS las ONUs afectadas de esta categoría (1 query), agrupadas
    // en memoria por puerto para usarlas al sincronizar el detalle.
    $stmtOnusTodas = $pdo->prepare(
        "SELECT o.OntOlt AS OltIdApi, g.IndexCard, g.IndexPort, o.OntId
         FROM onu o
         INNER JOIN gpon g ON g.IdOlt = o.OntGpon
         INNER JOIN potencia p ON p.Onu = o.OntId
         WHERE p.Status = :statusCode"
    );
    $stmtOnusTodas->execute([':statusCode' => $statusCode]);
    $onusPorPuerto = [];
    foreach ($stmtOnusTodas->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = outageEventsTask_clave($row['OltIdApi'], $row['IndexCard'], $row['IndexPort']);
        $onusPorPuerto[$key][] = (int) $row['OntId'];
    }

    // 3) Eventos abiertos ANTES de tocar nada (1 query). Se usa solo para
    // decidir qué cerrar; el upsert de abajo no necesita esto porque el
    // propio UNIQUE KEY resuelve crear-vs-actualizar en el servidor.
    $stmtAbiertos = $pdo->prepare(
        "SELECT IdEvent, OltIdApi, IndexCard, IndexPort FROM outage_events
         WHERE Categoria = :categoria AND EsAbierto = 1"
    );
    $stmtAbiertos->execute([':categoria' => $categoria]);
    $abiertosPorPuerto = [];
    foreach ($stmtAbiertos->fetchAll(PDO::FETCH_ASSOC) as $row) {
        $key = outageEventsTask_clave($row['OltIdApi'], $row['IndexCard'], $row['IndexPort']);
        $abiertosPorPuerto[$key] = (int) $row['IdEvent'];
    }

    // Clasificar en memoria (sin tocar la DB todavía)
    $paraCrearActualizar = []; // puertos con afectadas>0
    $idsParaCerrar = [];       // eventos abiertos cuyo puerto ya no tiene afectadas

    foreach ($puertos as $puerto) {
        $olt   = (int) $puerto['OltIdApi'];
        $card  = (int) $puerto['IndexCard'];
        $port  = (int) $puerto['IndexPort'];
        $total = (int) $puerto['Total'];
        $afectadas = (int) $puerto['Afectadas'];
        $key = outageEventsTask_clave($olt, $card, $port);

        if ($afectadas === 0) {
            if (isset($abiertosPorPuerto[$key])) {
                $idsParaCerrar[] = $abiertosPorPuerto[$key];
            }
            continue;
        }

        $tipoAlcance = ($afectadas === $total) ? 'total' : 'parcial';
        $paraCrearActualizar[] = [$olt, $card, $port, $categoria, $tipoAlcance, $afectadas, $total, $fecha, $fecha];
    }

    $cerrados = count($idsParaCerrar);
    if ($idsParaCerrar) {
        // 4) Cerrar TODOS los eventos resueltos en un solo UPDATE...IN(...)
        $placeholders = implode(',', array_fill(0, count($idsParaCerrar), '?'));
        $pdo->prepare("UPDATE outage_events SET FechaFin = ?, UltimaActualizacion = ? WHERE IdEvent IN ($placeholders)")
            ->execute(array_merge([$fecha, $fecha], $idsParaCerrar));
        // Y su detalle, también en un solo DELETE...IN(...)
        $pdo->prepare("DELETE FROM outage_event_onus WHERE IdEvent IN ($placeholders)")
            ->execute($idsParaCerrar);
    }

    $abiertos = count($paraCrearActualizar);
    if ($abiertos > 0) {
        // 5) Crear/actualizar en lotes multi-fila. El UNIQUE KEY
        // (OltIdApi, IndexCard, IndexPort, Categoria, EsAbierto) hace que
        // MySQL decida solo si es INSERT nuevo o UPDATE del abierto
        // existente — no hace falta que PHP pregunte antes por cada fila.
        // FechaInicio NO se toca en el UPDATE: si ya existía, se conserva
        // el inicio real del problema aunque haya escalado de parcial a total.
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

        // 6) Releer eventos abiertos YA con los recién creados incluidos
        // (1 query) para poder sincronizar el detalle por ONU.
        $stmtAbiertos->execute([':categoria' => $categoria]);
        $abiertosPorPuertoFresco = [];
        foreach ($stmtAbiertos->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $key = outageEventsTask_clave($row['OltIdApi'], $row['IndexCard'], $row['IndexPort']);
            $abiertosPorPuertoFresco[$key] = (int) $row['IdEvent'];
        }
        $idsAbiertosFrescos = array_values($abiertosPorPuertoFresco);

        // 7) Borrar TODO el detalle previo de los eventos que siguen
        // abiertos en un solo DELETE...IN(...), y volver a insertarlo
        // completo en lotes multi-fila (en vez de 1 INSERT por ONU).
        if ($idsAbiertosFrescos) {
            $placeholders = implode(',', array_fill(0, count($idsAbiertosFrescos), '?'));
            $pdo->prepare("DELETE FROM outage_event_onus WHERE IdEvent IN ($placeholders)")
                ->execute($idsAbiertosFrescos);
        }

        $filasDetalle = [];
        foreach ($paraCrearActualizar as $fila) {
            $key = outageEventsTask_clave($fila[0], $fila[1], $fila[2]);
            $idEvent = $abiertosPorPuertoFresco[$key] ?? null;
            if (!$idEvent) continue;
            foreach ($onusPorPuerto[$key] ?? [] as $ontId) {
                $filasDetalle[] = [$idEvent, $ontId, $fecha];
            }
        }
        if ($filasDetalle) {
            $sqlDetalleTemplate = "INSERT IGNORE INTO outage_event_onus (IdEvent, OntId, FechaDetectado) VALUES %s";
            outageEventsTask_insertarEnLotes($pdo, $sqlDetalleTemplate, '(?,?,?)', $filasDetalle, 500);
        }
    }

    return ['abiertos' => $abiertos, 'cerrados' => $cerrados];
}
