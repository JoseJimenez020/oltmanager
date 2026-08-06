<?php
/**
 * cron/tasks/fetch_olt_profile.php
 *
 * Ejecutado como SUBPROCESO AISLADO por profileOnu::getProfileAislado().
 * Procesa el perfil SNMP de UNA sola OLT y lo imprime como JSON a stdout.
 *
 * Existe porque set_time_limit() de PHP NO puede interrumpir de forma
 * confiable una llamada SNMP bloqueada en Windows (el motor solo revisa
 * el límite entre instrucciones; una llamada atascada dentro de la
 * extensión C no le da esa oportunidad). Corriendo cada OLT en su propio
 * proceso, el padre puede matarlo desde afuera con proc_terminate() si
 * se pasa del tiempo, sin depender de que el hijo "coopere".
 *
 * IMPORTANTE: este script NO llama set_time_limit() a propósito. El
 * único mecanismo de timeout es EXTERNO (proc_terminate() desde el
 * proceso padre) — así evitamos que dos mecanismos de límite distintos
 * (uno interno, uno externo) compitan o se pisen entre sí.
 *
 * Uso: php fetch_olt_profile.php <OltIdApi>
 * Salida por stdout: JSON del array de perfil (mismo formato que
 *   profileOnu::getProfile()), o "null" si falla/no hay datos.
 *   ÚNICAMENTE JSON limpio sale por stdout — cualquier warning/notice/
 *   salida accidental de PHP se captura y se descarta (se manda a
 *   stderr como diagnóstico), para que el proceso padre nunca reciba
 *   un JSON corrupto por un simple warning suelto de código heredado.
 * Código de salida: 0 si obtuvo datos, 1 si falló.
 */

// CRÍTICO: nunca imprimir errores a pantalla aquí — cualquier texto que
// no sea el JSON final rompería el json_decode() del proceso padre.
ini_set('display_errors', '0');
error_reporting(E_ALL);

// Se buffea TODA la salida del script. Al final solo se imprime el JSON
// limpio; cualquier otra cosa que se haya colado (warnings, notices,
// echos sueltos de clases heredadas) se descarta de stdout y se reporta
// aparte por stderr, sin corromper la respuesta.
ob_start();

require_once(__DIR__ . '/../../app/metodos/onuProfile/profileOnu.php');

$oltIdApi = $argv[1] ?? null;

if (!$oltIdApi) {
    ob_end_clean();
    fwrite(STDERR, "[fetch_olt_profile] Falta el parámetro OltIdApi\n");
    echo json_encode(null);
    exit(1);
}

$resultado = null;

try {
    $m = new profileOnu();
    // getProfile() es autosuficiente por OLT: no depende de self::$ol ni
    // self::$gpon (esos son estado compartido de getProfiles()/formatOnu()
    // en el proceso padre; no se necesitan ni están disponibles aquí, ya
    // que cada subproceso es una instancia de PHP completamente separada).
    $resultado = $m->getProfile($oltIdApi);
} catch (\Throwable $e) {
    fwrite(STDERR, "[fetch_olt_profile] Excepción para OLT $oltIdApi: " . $e->getMessage() . "\n");
    $resultado = null;
}

// Recuperar y descartar cualquier salida accidental acumulada en el
// buffer (warnings de SNMP, notices de PHP, etc.) — se reporta a stderr
// solo como diagnóstico, nunca se mezcla con el JSON de stdout.
$salidaAccidental = trim(ob_get_clean());
if ($salidaAccidental !== '') {
    fwrite(STDERR, "[fetch_olt_profile] Salida inesperada descartada para OLT $oltIdApi: "
        . substr($salidaAccidental, 0, 500) . "\n");
}

echo json_encode($resultado);
exit($resultado === null ? 1 : 0);