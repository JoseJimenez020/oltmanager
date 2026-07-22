<?php
/**
 * cron/tasks/outage_events_task.php
 *
 * Detecta y mantiene el estado de outage_events / outage_event_onus por
 * puerto GPON (OltIdApi + IndexCard + IndexPort), para las secciones del
 * dashboard "LOS parcial", "LOS total del PON", "Fallo de energía" y
 * "Offline N/A". Las cuatro comparten la misma lógica, solo cambia el
 * Status de potencia que se considera "afectado" por categoría.
 *
 * Debe ejecutarse DESPUÉS de refresh_db_task.php en el mismo ciclo, ya que
 * depende de que la tabla `potencia` tenga el Status recién actualizado por
 * SNMP. No abre ninguna sesión SNMP propia.
 *
 * Mapeo de categorías -> Status (ver controllers/refresh_onu.php::obtenerStatus
 * y los switch de status homólogos usados en todo el front, mismo mapeo):
 *   los      -> Status = 1  (corte de fibra / pérdida de señal óptica)
 *   pwrfail  -> Status = 4  (falla de energía en el equipo del cliente)
 *   offline  -> Status = 6  (fuera de línea, sin causa distinguible)
 *
 * NOTA: la función auxiliar usa el prefijo outageEventsTask_ a propósito.
 * run_all.php incluye varias tasks en el mismo proceso PHP, y sin un
 * namespace o clase, cualquier `function` de nivel global declarada aquí
 * queda visible para el resto del script; el prefijo reduce el riesgo de
 * choque con nombres de otras tasks presentes o futuras.
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

/**
 * Procesa una categoría completa: agrupa por puerto GPON, abre/actualiza/
 * cierra eventos, y sincroniza el detalle por ONU.
 */
function outageEventsTask_procesarCategoria(PDO $pdo, string $categoria, int $statusCode): array
{
    $fecha = date('Y-m-d H:i:s');
    $abiertos = 0;
    $cerrados = 0;

    // 1) Estado actual real por puerto: total de ONUs y cuántas están en el
    // Status de esta categoría, agrupado por OLT+tarjeta+puerto.
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

    // 2) ONUs afectadas por puerto, para sincronizar el detalle
    $stmtOnus = $pdo->prepare(
        "SELECT o.OntId
         FROM onu o
         INNER JOIN gpon g ON g.IdOlt = o.OntGpon
         INNER JOIN potencia p ON p.Onu = o.OntId
         WHERE o.OntOlt = :olt AND g.IndexCard = :card AND g.IndexPort = :port
           AND p.Status = :statusCode"
    );

    // 3) Buscar evento abierto existente para este puerto+categoria
    $stmtBuscarAbierto = $pdo->prepare(
        "SELECT IdEvent FROM outage_events
         WHERE OltIdApi = :olt AND IndexCard = :card AND IndexPort = :port
           AND Categoria = :categoria AND EsAbierto = 1
         LIMIT 1"
    );

    $stmtCerrar = $pdo->prepare(
        "UPDATE outage_events SET FechaFin = :fechaFin, UltimaActualizacion = :fechaActualizacion
         WHERE IdEvent = :id"
    );

    $stmtActualizar = $pdo->prepare(
        "UPDATE outage_events
         SET TipoAlcance = :tipo, OnusAfectadas = :afectadas, OnusTotal = :total,
             UltimaActualizacion = :fecha
         WHERE IdEvent = :id"
    );

    $stmtCrear = $pdo->prepare(
        "INSERT INTO outage_events
            (OltIdApi, IndexCard, IndexPort, Categoria, TipoAlcance,
             OnusAfectadas, OnusTotal, FechaInicio, UltimaActualizacion)
         VALUES (:olt, :card, :port, :categoria, :tipo, :afectadas, :total, :fechaInicio, :fechaActualizacion)"
    );

    $stmtBorrarDetalle = $pdo->prepare("DELETE FROM outage_event_onus WHERE IdEvent = :id");
    $stmtInsertarDetalle = $pdo->prepare(
        "INSERT IGNORE INTO outage_event_onus (IdEvent, OntId, FechaDetectado)
         VALUES (:id, :ontId, :fecha)"
    );
    // Estrategia de sincronización del detalle: borrar todo + reinsertar el
    // estado actual en cada ciclo (usado dentro del loop más abajo). Con
    // pocas ONUs por puerto (típicamente <64) es más simple y seguro que
    // un diff incremental fila por fila.

    foreach ($puertos as $puerto) {
        $olt   = (int) $puerto['OltIdApi'];
        $card  = (int) $puerto['IndexCard'];
        $port  = (int) $puerto['IndexPort'];
        $total = (int) $puerto['Total'];
        $afectadas = (int) $puerto['Afectadas'];

        $stmtBuscarAbierto->execute([':olt' => $olt, ':card' => $card, ':port' => $port, ':categoria' => $categoria]);
        $abierto = $stmtBuscarAbierto->fetch(PDO::FETCH_ASSOC);

        if ($afectadas === 0) {
            // Sin problema en este puerto: cerrar evento si había uno abierto
            if ($abierto) {
                $stmtCerrar->execute([':fechaFin' => $fecha, ':fechaActualizacion' => $fecha, ':id' => $abierto['IdEvent']]);
                $stmtBorrarDetalle->execute([':id' => $abierto['IdEvent']]);
                $cerrados++;
            }
            continue;
        }

        $tipoAlcance = ($afectadas === $total) ? 'total' : 'parcial';

        if ($abierto) {
            $idEvent = $abierto['IdEvent'];
            $stmtActualizar->execute([
                ':tipo' => $tipoAlcance, ':afectadas' => $afectadas, ':total' => $total,
                ':fecha' => $fecha, ':id' => $idEvent,
            ]);
        } else {
            $stmtCrear->execute([
                ':olt' => $olt, ':card' => $card, ':port' => $port, ':categoria' => $categoria,
                ':tipo' => $tipoAlcance, ':afectadas' => $afectadas, ':total' => $total,
                ':fechaInicio' => $fecha, ':fechaActualizacion' => $fecha,
            ]);
            $idEvent = (int) $pdo->lastInsertId();
        }
        $abiertos++;

        // Sincronizar detalle por ONU con el estado actual de este puerto
        $stmtBorrarDetalle->execute([':id' => $idEvent]);
        $stmtOnus->execute([':olt' => $olt, ':card' => $card, ':port' => $port, ':statusCode' => $statusCode]);
        foreach ($stmtOnus->fetchAll(PDO::FETCH_ASSOC) as $row) {
            $stmtInsertarDetalle->execute([':id' => $idEvent, ':ontId' => $row['OntId'], ':fecha' => $fecha]);
        }
    }

    return ['abiertos' => $abiertos, 'cerrados' => $cerrados];
}
