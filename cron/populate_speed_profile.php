<?php
/**
 * cron/populate_speed_profile.php
 * DEPLOY: C:\xampp\htdocs\oltmanager\cron\populate_speed_profile.php
 *
 * Para cuando des de alta una OLT nueva: lee por SNMP sus perfiles reales
 * TCONT/GEMPORT (IndexProfile + ProfileName) y los inserta en
 * speed_profile_olts. No copia valores de otra OLT porque el índice de
 * perfil es local a cada equipo (confirmado: "Use prefix&suffix" distinto
 * entre OLTs aunque el nombre del perfil coincida).
 *
 * Idempotente por default: si la zona ya tiene perfiles, se omite (para no
 * duplicar). Usa --force si de verdad quieres reinsertar.
 *
 * USO:
 *   php populate_speed_profile.php 38,39,40,41
 *   php populate_speed_profile.php 42                (una sola OLT nueva)
 *   php populate_speed_profile.php 38 --force
 */

require_once(__DIR__ . '/../app/metodos/speedProfile/speedProfileOlt.php');

$argvOlts = $argv[1] ?? '';
$force    = in_array('--force', $argv, true);

if ($argvOlts === '') {
    fwrite(STDERR, "Uso: php populate_speed_profile.php OltIdApi1,OltIdApi2,... [--force]\n");
    exit(1);
}

$oltIds = array_map('intval', explode(',', $argvOlts));
$speedOlt = new speedProfileOlt();

echo "Modo: " . ($force ? "FORCE (reinserta aunque ya existan)" : "SEGURO (omite zonas que ya tienen perfiles)") . "\n\n";

foreach ($oltIds as $oltIdApi) {
    $r = $speedOlt->insertSpeedForOlt($oltIdApi, $force);

    switch ($r['status']) {
        case 'ok':
            echo "[OltIdApi {$oltIdApi}] OK — {$r['perfiles_insertados']} perfiles insertados.\n";
            break;
        case 'omitido':
            echo "[OltIdApi {$oltIdApi}] Omitido — {$r['motivo']}\n";
            break;
        case 'error':
            echo "[OltIdApi {$oltIdApi}] ERROR — {$r['motivo']}\n";
            break;
    }
}

echo "\nListo.\n";