<?php
require_once(__DIR__ . '/migracionDb.php');
class migracionController{
    private $db;

    public function __construct()
    {
        $this->db = new migracionDb();
    }
    public function insertMigracions($r){
        date_default_timezone_set('America/Merida');
        $date = date("Y-m-d H:i:s");
        
        return $this->db->insertMigracions($r,$date);
    }
    public function updateMigracion($r){
        $res = $this->db->updateMigracion($r);
        return $res;
    }
    public function getMigracions(){
        $r = $this->db->getMigracions();
        return $this->migracionsFormat($r);
    }
    private function migracionsFormat($r){
        $format = [];
        foreach ($r as $k) {
            $format[] = [
            'Id' => $k['OntId'],
            'Fecha' => $k['date'],
            'GponNuevo' => $k['cardNuevo'] . '/' . $k['portNuevo'] . ':' . $k['posNuevo'],
            'Nombre' => $k['OntNombre'],
            'Zona' => $k['OltName'],
            'GponViejo' => $k['cardViejo'] . '/' . $k['portViejo'] . ':' . $k['OntPos']
            ];
        }
        return $format;
    }
    public function getGponById($id){
        $r = $this->db->getGponById($id);
        return $r;
    }
    public function updateMigracionMany($r){
        $migracion = $this->getGponById($r);
        if(empty($migracion)) return false;
        $re = $this->updateMigracion($migracion);
        if ($re == false) return false;
        return $this->db->deleteMigracion($migracion);
    }
    
}
