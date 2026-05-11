<?php
require_once(__DIR__ . '/potenciaDb.php');

class potenciaController{
    private $db;

    public function __construct(){
        $this->db = new potenciaDb();
    }
    
    public function insertOnePotencia($onu){
        date_default_timezone_set('America/Merida');
        $date= date("Y-m-d H:i:s");
        $p=[
            'rx'=>-19000,
            'sta'=>0,
            'dis'=>0,
            'id'=>$onu
        ];
        $result = $this->db->insertOnePotencia($p,$date);
        return $result;
    }
    public function insertPotencia($onu,$potencia){
        date_default_timezone_set('America/Merida');
        $date = date("Y-m-d H:i:s");
        $p = $this->getPotenciaOnus($onu,$potencia);
        $result = $this->db->insertPotencia($p,$date);
        return $result;
    }
    public function getPotenciaOnus($onus,$potencia){
        $p=[];
        
        foreach ($potencia as $o) {
            $find = @$onus[$o['sn']];
            if (isset($find)) {
                $p[] =[
                    'id'=> $find['OntId'],
                    'rx'=> $o['rx'],
                    'dis'=> $o['dis'],
                    'sta'=> $o['status']
                ]; 
            }
        }
        return $p;
    }
    public function updateStatus($sta){
        $result=$this->db->updateStatusOnus($sta);
        return $result;
    }
}