<?php
/**
 * cron/oneoff_populate_gpon.php
 * DEPLOY: C:\xampp\htdocs\oltmanager\cron\oneoff_populate_gpon.php
 *
 * PROBLEMA QUE RESUELVE:
 *   Las OLTs El Tigre (38), Chihuahua (39), Canek-Merida (40) y Zaragoza (41)
 *   nunca tuvieron filas en `gpon`/`intgpon`. Sin esas filas, profileOnu::getProfile()
 *   descarta TODAS sus ONUs en el `continue` de:
 *     if (!isset($g[$indexOid])) continue;
 *   (ver app/metodos/onuProfile/onuDb.php::getCard())
 *
 * QUÉ HACE:
 *   Para cada OltIdApi indicado, camina OnuName (.28.1.1.2) por SNMP para
 *   descubrir los IndexOid realmente en uso (los mismos que ya trae cada
 *   ONU en su OID). Los agrupa en "tarjetas" detectando saltos != 256 entre
 *   valores consecutivos (dentro de una misma tarjeta ZTE, los puertos
 *   quedan espaciados exactamente 256 unidades, confirmado con datos reales
 *   de `central` y de El Tigre). Inserta 1 fila en `intgpon` + 1 fila en
 *   `gpon` por cada IndexOid que aún no exista.
 *
 * NOTA IMPORTANTE:
 *   `gpon.IdOlt` es un PK autoincremental SIN relación formal con
 *   `olts_list.OltIdApi` (revisado en el schema, sin FK entre ambas). El
 *   join real usa solo IndexOid, así que la identidad "de qué OLT física es
 *   esta fila" no vive en la tabla — igual que en las filas ya existentes de
 *   `central`. Este script asume que IndexOid no colisiona entre OLTs
 *   distintas (así ha funcionado hasta ahora); si algún IndexOid ya existe
 *   en `gpon`, se omite y se reporta como advertencia en vez de sobrescribir.
 *
 * USO:
 *   php oneoff_populate_gpon.php 38,39,40,41            (dry-run, solo muestra)
 *   php oneoff_populate_gpon.php 38,39,40,41 --commit    (aplica de verdad)
 *   php oneoff_populate_gpon.php 38 --commit             (una sola OLT)
 **/

require_once(__DIR__ . '/../db/conn.php');
require_once(__DIR__ . '/../app/class/OidOnu.php');
require_once(__DIR__ . '/../app/metodos/oltProfile/oltProfileDb.php');

$argvOlts = $argv[1] ?? '';
$commit   = in_array('--commit', $argv, true);

if ($argvOlts === '') {
    fwrite(STDERR, "Uso: php oneoff_populate_gpon.php OltIdApi1,OltIdApi2,... [--commit]\n");
    exit(1);
}

$oltIds = array_map('intval', explode(',', $argvOlts));

$pdo = (new DbConn())->getPdo();
$oltDb = new oltProfile();
$onu = new Onu();
$onuOid = $onu->onuOid();

echo "Modo: " . ($commit ? "COMMIT (se va a escribir en la BD)" : "DRY-RUN (solo revisión, nada se escribe)") . "\n\n";

foreach ($oltIds as $oltIdApi) {

    $perfil = $oltDb->GetSnmpProfile((string) $oltIdApi);
    if (!$perfil) {
        echo "[OltIdApi $oltIdApi] No encontrada en olts_list, se omite.\n\n";
        continue;
    }
    $oltName = $perfil['OltName'];
    echo "=== OltIdApi $oltIdApi ($oltName) ===\n";

    // ---- 1. Walk SNMP: descubrir IndexOid en uso ----
    $session = new SNMP(SNMP::VERSION_2c, $perfil['OltIpPrivate'], $perfil['ReadComm']);
    $session->quick_print = 1;
    $result = @$session->walk($onuOid['OnuName'], true);
    $err = $session->getErrno();
    $session->close();

    if ($err !== 0) {
        echo "  ERROR SNMP (errno $err), se omite esta OLT.\n\n";
        continue;
    }
    if (empty($result)) {
        echo "  Walk vacío, no hay ONUs que mapear. Se omite.\n\n";
        continue;
    }

    $indexOidsEnUso = [];
    foreach (array_keys($result) as $key) {
        // $key luce como "268632576.1" -> nos interesa solo la parte antes del último punto
        $pos = strrpos($key, '.');
        if ($pos === false) continue;
        $indexOid = substr($key, 0, $pos);
        $indexOidsEnUso[$indexOid] = true;
    }
    $indexOidsEnUso = array_map('intval', array_keys($indexOidsEnUso));
    sort($indexOidsEnUso);

    echo "  IndexOid distintos encontrados en el walk: " . count($indexOidsEnUso) . "\n";

    // ---- 2. Filtrar los que ya existen en gpon (para no duplicar / no chocar) ----
    $stmtExiste = $pdo->prepare("SELECT IndexOid FROM gpon WHERE IndexOid = ?");
    $nuevos = [];
    $yaExisten = [];
    foreach ($indexOidsEnUso as $indexOid) {
        $stmtExiste->execute([(string) $indexOid]);
        if ($stmtExiste->fetch()) {
            $yaExisten[] = $indexOid;
        } else {
            $nuevos[] = $indexOid;
        }
    }

    if (!empty($yaExisten)) {
        echo "  AVISO: " . count($yaExisten) . " IndexOid ya existen en `gpon` (se omiten, posible colisión con otra OLT): "
            . implode(', ', array_slice($yaExisten, 0, 10)) . (count($yaExisten) > 10 ? '...' : '') . "\n";
    }

    if (empty($nuevos)) {
        echo "  Nada nuevo que insertar para esta OLT.\n\n";
        continue;
    }

    // ---- 3. Agrupar en "tarjetas": salto != 256 respecto al anterior = tarjeta nueva ----
    $tarjetas = [];      // cada elemento: array de IndexOid consecutivos (misma tarjeta)
    $actual = [];
    $anterior = null;
    foreach ($nuevos as $indexOid) {
        if ($anterior !== null && ($indexOid - $anterior) !== 256) {
            $tarjetas[] = $actual;
            $actual = [];
        }
        $actual[] = $indexOid;
        $anterior = $indexOid;
    }
    if (!empty($actual)) {
        $tarjetas[] = $actual;
    }

    echo "  Tarjetas detectadas (grupos de puertos consecutivos): " . count($tarjetas) . "\n";

    // ---- 4. Insertar intgpon + gpon ----
    $pdo->beginTransaction();
    $insertIntGpon = $pdo->prepare(
        "INSERT INTO intgpon (IndexIntGpon, NameIntGpon) VALUES (?, ?)"
    );
    $insertGpon = $pdo->prepare(
        "INSERT INTO gpon (IndexOid, IndexName, IndexPort, IndexCard, IdGpon) VALUES (?, ?, ?, ?, ?)"
    );

    $contadorGlobal = 0;
    $numeroTarjeta = 1;
    $filasPreview = [];

    foreach ($tarjetas as $grupo) {
        $puerto = 1;
        foreach ($grupo as $indexOid) {
            $contadorGlobal++;
            $indexName = "{$oltName}-{$contadorGlobal}";
            $nameIntGpon = "gpon_auto_{$oltIdApi}_{$numeroTarjeta}_{$puerto}";

            $filasPreview[] = [$indexOid, $indexName, $puerto, $numeroTarjeta, $nameIntGpon];

            if ($commit) {
                $insertIntGpon->execute([(string) $indexOid, $nameIntGpon]);
                $idIntGpon = $pdo->lastInsertId();
                $insertGpon->execute([(string) $indexOid, $indexName, $puerto, $numeroTarjeta, $idIntGpon]);
            }

            $puerto++;
        }
        $numeroTarjeta++;
    }

    if ($commit) {
        $pdo->commit();
        echo "  Insertadas " . count($filasPreview) . " filas en gpon/intgpon.\n";
    } else {
        $pdo->rollBack();
        echo "  (dry-run) Se insertarían " . count($filasPreview) . " filas. Muestra de las primeras 10:\n";
        foreach (array_slice($filasPreview, 0, 10) as $f) {
            echo "    IndexOid={$f[0]}  IndexName={$f[1]}  Card={$f[3]}  Port={$f[2]}  IntGpon={$f[4]}\n";
        }
    }

    echo "\n";
}

echo "Listo.\n";