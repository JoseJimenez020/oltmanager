<?php
require_once(__DIR__ . '/speedProfileDb.php');

class speedProfileController{
    private $db;
    
    public function __construct(){
        $this->db = new speedProfile();
    }

    public function GetSpeedProfile(){
        $speed=$this->db->GetSpeedProfile();
        return $speed;
    }
    public function GetSpeedTypeProfile($type,$zone){
        $speed=$this->db->GetSpeedTypeProfile($type,$zone);
        return $speed;
    }
    public function getSpeedFormByIdOnu($id,$type){
        $result = $this->db->getSpeedFormByIdOnu($id,$type);
        return $result;
    }
    public function InsertSP($name,$zona,$tipo,$speed){
        
        $result = $this->db->InsertSpeedProfile($name,$zona,$tipo,$speed);

        if ($result) {
            return "insertado";
        }else{
            return "error";
        }
    }

    public function GetOne($id){
        $result = $this->db->GetOneSpeed($id);

        return $result;
    }
    
    public function DeleteOne($id){
        $result = $this->db->DeleteOne($id);

        return $result;
    }

    public function getSpeed($type,$zona){
        $result = $this->db->getSpeed($type,$zona);
        return $result;
    }
    public function insertSpeedOlts($v,$o){
        $result = $this->db->insertSpeedOlts($v,$o);
        return $result;
    }
}