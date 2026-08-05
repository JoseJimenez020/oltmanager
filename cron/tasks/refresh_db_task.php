<?php
/**
 * cron/tasks/refresh_db_task.php
 *
 * Versión CLI de api/refreshDb.php, incluida por cron/run_all.php.
 *
 * INSTRUMENTADO: cada paso después de getProfiles()/getVlans()/getBands()
 * (que ya loguean su propio tiempo por OLT dentro de profileOnu.php /
 * bandWithController.php) ahora también registra su tiempo total, para
 * localizar los ~537s que quedaban sin explicar en el ciclo completo.
 */

set_time_limit(600);
ini_set('max_execution_time', 600);

require_once(__DIR__ . '/../../app/metodos/onuProfile/profileOnu.php');
require_once(__DIR__ . '/../../app/metodos/potencia/statusOnu.php');
require_once(__DIR__ . '/../../app/metodos/migracion/migracionStatus.php');
require_once(__DIR__ . '/../../app/metodos/bandWith/bandWithController.php');

function refreshDbTask_log(string $paso, float $inicio): void
{
    $elapsed = round(microtime(true) - $inicio, 2);
    error_log("[refresh_db_task] $paso tardó {$elapsed}s");
}

$band = new bandWithController();

try {
    $m = new profileOnu();
    $m->setOlt();

    // Ya logueado internamente por OLT (profileOnu::getProfiles)
    $profile = $m->getProfiles();

    $t = microtime(true);
    $m->setGponOnu();
    refreshDbTask_log('setGponOnu (1a llamada)', $t);

    $t = microtime(true);
    $update = $m->formatOnu($profile); // incluye flushRxHistory() ya en lotes
    refreshDbTask_log('formatOnu (incluye flush de historial_potencia)', $t);

    $t = microtime(true);
    $s = new statusOnu();
    $status = $s->updateStatus($update['existe']);
    refreshDbTask_log('updateStatus', $t);

    $t = microtime(true);
    $onus = $m->insertOnus($update['nuevo']);
    refreshDbTask_log('insertOnus', $t);

    $t = microtime(true);
    $m->unsetGponOnu();
    $m->setGponOnu();
    refreshDbTask_log('setGponOnu (2a llamada, tras insertOnus)', $t);

    $t = microtime(true);
    $potencia = $m->insertPotencia($update['nuevo']);
    refreshDbTask_log('insertPotencia', $t);

    // Ya logueado internamente por OLT (profileOnu::getVlans)
    $vlans = $m->getVlans();

    $t = microtime(true);
    $new  = $m->newVlans($update, $vlans);
    $vlan = $m->insertVlans($new);
    refreshDbTask_log('newVlans + insertVlans', $t);

    $t = microtime(true);
    $mig = new migracionStatus();
    $migracion = $mig->insertMigracion($update['migracion']);
    refreshDbTask_log('insertMigracion', $t);

    sleep(1);

    // getBands() ya logueado internamente por OLT (bandWithController).
    // insertBand() internamente llama getBands() + el INSERT real a DB;
    // medimos el total para ver cuánto de esto es la escritura vs la
    // lectura SNMP ya contabilizada por separado.
    $t = microtime(true);
    $band->insertBand();
    refreshDbTask_log('band->insertBand (incluye getBands + INSERT a DB)', $t);

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
    throw $e;
}