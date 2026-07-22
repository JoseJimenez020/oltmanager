<?php
/**
 * cron/tasks/refresh_db_task.php
 *
 * Versión CLI de api/refreshDb.php, pensada para ser incluida por
 * cron/run_all.php mediante `require`. NO usa header() ni echo json_encode();
 * en vez de eso retorna un string-resumen que run_all.php imprime.
 *
 * Mantiene exactamente la misma secuencia de operaciones que el endpoint web
 * original: leer perfil SNMP de todas las OLTs, clasificar ONUs (existe /
 * nuevo / migracion) -- lo cual ya dispara los inserts de historial_potencia
 * dentro de profileOnu::formatOnu() -- actualizar status/potencia, insertar
 * ONUs nuevas, vlans nuevas, migraciones, y banda ancha.
 *
 * IMPORTANTE: este archivo asume que fue incluido desde cron/run_all.php
 * (dos niveles bajo la raíz del proyecto: cron/tasks/). Si se ejecuta
 * standalone, ajustar las rutas de require_once.
 */

set_time_limit(600);
ini_set('max_execution_time', 600);

require_once(__DIR__ . '/../../app/metodos/onuProfile/profileOnu.php');
require_once(__DIR__ . '/../../app/metodos/potencia/statusOnu.php');
require_once(__DIR__ . '/../../app/metodos/migracion/migracionStatus.php');
require_once(__DIR__ . '/../../app/metodos/bandWith/bandWithController.php');

$band = new bandWithController();

try {
    $m = new profileOnu();
    $m->setOlt();

    // Lee SNMP de todas las OLTs (getProfiles() itera self::$ol internamente)
    $profile = $m->getProfiles();

    // Necesario ANTES de formatOnu(): construye self::$gpon, el mapa
    // serial -> {OntId, IndexOid, OntPos} contra el que se compara cada ONU.
    $m->setGponOnu();

    // Clasifica cada ONU leída por SNMP en existe/migracion/nuevo.
    // Efecto colateral (por diseño, ver conversación previa): cada ONU en
    // 'existe' o 'migracion' ya queda registrada en historial_potencia
    // dentro de este mismo método, sin sesión SNMP adicional.
    $update = $m->formatOnu($profile);

    // Actualiza status/potencia de ONUs que ya existían con el mismo index
    $s      = new statusOnu();
    $status = $s->updateStatus($update['existe']);

    // Inserta ONUs nuevas detectadas por SNMP que no estaban en DB
    $onus = $m->insertOnus($update['nuevo']);

    // Refresca el mapa self::$gpon (las recién insertadas ya tienen OntId)
    // antes de insertar su potencia inicial
    $m->unsetGponOnu();
    $m->setGponOnu();
    $potencia = $m->insertPotencia($update['nuevo']);

    // VLANs nuevas asociadas a ONUs recién detectadas
    $vlans = $m->getVlans();
    $new   = $m->newVlans($update, $vlans);
    $vlan  = $m->insertVlans($new);

    // Migraciones (ONU que cambió de card/puerto pero es el mismo equipo)
    $mig       = new migracionStatus();
    $migracion = $mig->insertMigracion($update['migracion']);

    sleep(1);
    $band->insertBand();

    return sprintf(
        'Actualiza: %s | Inserta: %s | Potencia: %s | Vlan: %s | Migracion: %s',
        var_export($status, true),
        var_export($onus, true),
        var_export($potencia, true),
        var_export($vlan, true),
        var_export($migracion, true)
    );

} catch (\Throwable $e) {
    error_log('[refresh_db_task] Excepción: ' . $e->getMessage());
    // Re-lanzamos para que runStep() en run_all.php la capture, la loguee
    // con su propio formato y continúe con los pasos siguientes.
    throw $e;
}