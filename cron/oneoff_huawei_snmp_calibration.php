<?php
/**
 * cron/oneoff_huawei_snmp_calibration.php
 *
 * Script de UNA SOLA VEZ para calibrar los OIDs de Huawei antes de
 * escribir OidOnuHuawei.php en definitivo. Hace walk de SN, RunStatus
 * y RxOlt y los junta por índice (ifIndex.OntId) para que puedas cruzar
 * el resultado contra la tabla que ya tienes por CLI
 * (display ont info 0 1 0 all).
 *
 * Uso:
 *   php cron/oneoff_huawei_snmp_calibration.php <ip_olt> <community_lectura>
 *
 * Ejemplo:
 *   php cron/oneoff_huawei_snmp_calibration.php 192.168.90.200 public
 */

if ($argc < 3) {
    echo "Uso: php cron/oneoff_huawei_snmp_calibration.php <ip_olt> <community_lectura>\n";
    exit(1);
}

$ip = $argv[1];
$community = $argv[2];

const OID_SN        = '.1.3.6.1.4.1.2011.6.128.1.1.2.43.1.3';  // hwGponDeviceOntSn
const OID_RUNSTATUS  = '.1.3.6.1.4.1.2011.6.128.1.1.2.46.1.15'; // hwGponDeviceOntControlRunStatus
const OID_RXOLT      = '.1.3.6.1.4.1.2011.6.128.1.1.2.51.1.6';  // hwGponOntOpticalDdmOltRxOntPower

echo "Conectando a $ip (community: $community)...\n\n";

function walkOid(string $ip, string $community, string $oid): array
{
    $session = new SNMP(SNMP::VERSION_2c, $ip, $community, 2000000, 3);
    $session->quick_print = 1;
    $session->oid_output_format = SNMP_OID_OUTPUT_SUFFIX; // deja solo el sufijo (el índice)
    $result = @$session->walk($oid, true);
    $err = $session->getErrno();
    $session->close();

    if ($err !== 0) {
        echo "ERROR al hacer walk de $oid: errno $err (" . $session->getError() . ")\n";
        return [];
    }
    if (!$result) {
        echo "Walk de $oid no devolvió datos (¿OID incorrecto o vacío en este equipo?)\n";
        return [];
    }
    return $result;
}

echo "--- Serial (SN) ---\n";
$sn = walkOid($ip, $community, OID_SN);
foreach ($sn as $idx => $val) {
    echo "$idx => $val\n";
}

echo "\n--- Estado (RunStatus) ---\n";
$status = walkOid($ip, $community, OID_RUNSTATUS);
foreach ($status as $idx => $val) {
    echo "$idx => $val\n";
}

echo "\n--- RX (OLT recibe de la ONU) ---\n";
$rx = walkOid($ip, $community, OID_RXOLT);
foreach ($rx as $idx => $val) {
    echo "$idx => $val\n";
}

echo "\n--- TABLA CRUZADA (por índice) ---\n";
echo str_pad("Indice", 20) . str_pad("SN", 20) . str_pad("RunStatus", 12) . "RX\n";
foreach ($sn as $idx => $serial) {
    $st = $status[$idx] ?? '?';
    $rxVal = $rx[$idx] ?? '?';
    echo str_pad($idx, 20) . str_pad($serial, 20) . str_pad($st, 12) . $rxVal . "\n";
}