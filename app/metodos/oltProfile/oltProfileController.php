<?php
require_once(__DIR__ . '/oltProfileDb.php');

class oltProfileController{
    private $db;

    public function __construct(){
        $this->db = new oltProfile();
    }

    public function GetOlt(){
        $olt=$this->db->GetOlt();
        return $olt;
    }
    public function getOltList(){
        $result = $this->db->getOltList();
        return $result;
    }
    public function GetOne($id){
        $result = $this->db->GetOneOlt($id);

        return $result;
    }
    public function GetSnmpProfile($host){
        $result =$this->db->GetSnmpProfile($host);

        return $result;
    }
    public function UpdateOltProfile($data){
        $result = $this->db->UpdateOltProfile($data);
        return $result;
    }
    public function getIndexOlt($olt = null){
        $result = $this->db->getIndexOlt($olt);
        return $result;
    }
    
    public function getVlanMgmtOlt($zona){
        $result = $this->db->getVlanMgmtOlt($zona);
        return $result;
    }
    public function getVlansMgmtByOlt($zona){
        $result = $this->db->getVlansMgmtByOlt($zona);
        return $result;
    }
}