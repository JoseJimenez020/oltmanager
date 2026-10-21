<?php
//app/métodos/speedProfile/speedProfileOlt.php
require_once(__DIR__ . '../../snmp/speedProfile.php');
require_once(__DIR__ . '/speedProfileController.php');
require_once(__DIR__ . '/speedProfileDb.php');
require_once(__DIR__ . '../../oltProfile/oltProfileController.php');

class speedProfileOlt{
    private $speed;
    private $olt;

    public function __construct(){
        $this->speed = new speedProfileController();
        $this->olt = new oltProfileController();
    }
    public function insertSpeed(){
        $o=$this->olt->GetOlt();
        foreach ($o as $k) {
            $spe=new speedProfileS($k['OltIdApi'],'read');
            $this->speed->insertSpeedOlts($spe->getSpeedProfile(),$k['OltIdApi']);
        }
    }

    /**
     * Extrae por SNMP los perfiles TCONT/GEMPORT reales de UNA OLT y los
     * inserta en speed_profile_olts. Pensado para dar de alta OLTs nuevas
     * sin tener que copiar a mano perfiles de otra OLT (los índices
     * IndexProfile son locales a cada equipo y no coinciden entre OLTs).
     *
     * Idempotente por default: si la zona ya tiene perfiles, no hace nada
     * (evita duplicar lo que ya funciona en OLTs existentes). Usa
     * $force=true para forzar de todos modos.
     *
     * @return array ['status'=>'ok'|'omitido'|'error', 'oltIdApi'=>..., ...]
     */
    public function insertSpeedForOlt($oltIdApi, bool $force = false): array
    {
        $db = new speedProfile();

        if (!$force) {
            $existentes = $db->GetSpeedTypeProfile('up', $oltIdApi);
            if (!empty($existentes)) {
                return [
                    'status'   => 'omitido',
                    'motivo'   => 'La zona ya tiene perfiles de velocidad (usa force=true para forzar)',
                    'oltIdApi' => $oltIdApi,
                ];
            }
        }

        $spe = new speedProfileS((string) $oltIdApi, 'read');
        $data = $spe->getSpeedProfile();
        $spe->close();

        // getTcont()/getGemport() devuelven ['error'=>...] cuando no hay
        // sesión válida o el walk falla; dataArray() los cuela como si
        // fueran un índice más (index[]='error'). Lo detectamos aquí para
        // no insertar basura.
        if (empty($data['index']) || in_array('error', $data['index'], true)) {
            return [
                'status'   => 'error',
                'motivo'   => 'El walk SNMP de TCONT/GEMPORT no devolvió datos válidos (revisar credenciales/host en olts_list)',
                'oltIdApi' => $oltIdApi,
            ];
        }

        $this->speed->insertSpeedOlts($data, $oltIdApi);

        return [
            'status'              => 'ok',
            'oltIdApi'            => $oltIdApi,
            'perfiles_insertados' => count($data['index']),
        ];
    }
}