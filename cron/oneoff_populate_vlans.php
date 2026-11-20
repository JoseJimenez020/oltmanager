<?php
/**
 * cron/oneoff_populate_vlans.php
 *
 * Puebla vlans_olt para OLTs nuevas.
 *
 * USO:
 *   php oneoff_populate_vlans.php 36,38,39,40,41           (dry-run, solo muestra)
 *   php oneoff_populate_vlans.php 36,38,39,40,41 --commit  (escribe)
 *   php oneoff_populate_vlans.php 42 --copy-from=6 --commit (clona las VLANs de la OLT 6)
 *
 * Es idempotente: no inserta una VLAN que ya exista para esa OLT
 * (mismo Vlan + VlanOltId).
 */

require_once(__DIR__ . '/../db/conn.php');
require_once(__DIR__ . '/../app/metodos/snmp/oltSnmp.php');
require_once(__DIR__ . '/../app/metodos/snmp/profileOnu.php');

$argvOlts = $argv[1] ?? '';
$commit   = in_array('--commit', $argv, true);
$copyFrom = null;
foreach ($argv as $a) {
    if (strpos($a, '--copy-from=') === 0) {
        $copyFrom = (int) substr($a, 12);
    }
}

if ($argvOlts === '' || $argvOlts[0] === '-') {
    fwrite(STDERR, "Uso: php oneoff_populate_vlans.php OltIdApi1,OltIdApi2 [--copy-from=ID] [--commit]\n");
    exit(1);
}

$oltIds = array_map('intval', explode(',', $argvOlts));
$pdo    = (new DbConn())->getPdo();

echo "Modo: " . ($commit ? "COMMIT" : "DRY-RUN (nada se escribe)") . "\n\n";

$stmtExiste = $pdo->prepare("SELECT 1 FROM vlans_olt WHERE VlanOltId = ? AND Vlan = ? LIMIT 1");
$stmtInsert = $pdo->prepare(
    "INSERT INTO vlans_olt (VlanIdentity, Vlan, VlanDescription, VlanOltId, VlanScope)
     VALUES (?, ?, ?, ?, ?)"
);

foreach ($oltIds as $oltId) {
    echo "=== OltIdApi $oltId ===\n";

    // ---- 1. Obtener la lista de VLANs candidatas: [vlan => scope] ----
    $candidatas = [];

    if ($copyFrom) {
        $q = $pdo->prepare("SELECT Vlan, VlanDescription, VlanScope FROM vlans_olt WHERE VlanOltId = ?");
        $q->execute([$copyFrom]);
        foreach ($q->fetchAll(PDO::FETCH_ASSOC) as $r) {
            $candidatas[$r['Vlan']] = [$r['VlanScope'], $r['VlanDescription']];
        }
        echo "  Origen: clon de la OLT $copyFrom (" . count($candidatas) . " VLANs)\n";
    } else {
        $snmp = new nSnmp((string) $oltId, 'read');
        $s    = new profileOnuS($snmp);
        $walk = $s->getWalk('VLAN');
        $s->close();

        if (empty($walk)) {
            echo "  Walk SNMP vacío o con error (¿credenciales/ACL? ¿OLT sin ONUs?). Usa --copy-from.\n\n";
            continue;
        }

        // Clave con sufijo: indexOid.pos.vport.N  => valor = número de VLAN
        foreach ($walk as $key => $value) {
            $partes = explode('.', $key);
            if (count($partes) < 3) continue;
            $vport = (int) $partes[2];
            $vlan  = (int) trim((string) $value, "\" ");
            if ($vlan <= 0 || $vlan > 4094) continue;

            $scope = ($vport === 2) ? 'mgmt/voip' : 'internet';
            // Si una misma VLAN aparece como internet y mgmt, gana internet
            if (!isset($candidatas[$vlan]) || $scope === 'internet') {
                $candidatas[$vlan] = [$scope, "Auto-detectada (SNMP)"];
            }
        }
        ksort($candidatas);
        echo "  VLANs distintas detectadas por SNMP: " . count($candidatas) . "\n";
    }

    // ---- 2. Insertar las que falten ----
    $nuevas = 0;
    foreach ($candidatas as $vlan => [$scope, $desc]) {
        $stmtExiste->execute([$oltId, (string) $vlan]);
        if ($stmtExiste->fetchColumn()) {
            echo "  = VLAN $vlan ya existe, se omite\n";
            continue;
        }
        echo "  + VLAN $vlan  [$scope]  $desc\n";
        if ($commit) {
            $stmtInsert->execute([(string) $vlan, (string) $vlan, $desc, $oltId, $scope]);
        }
        $nuevas++;
    }
    echo "  " . ($commit ? "Insertadas" : "Se insertarían") . ": $nuevas\n\n";
}

echo "Listo.\n";