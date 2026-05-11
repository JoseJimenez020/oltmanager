<?php
require_once(__DIR__ . '/ipsDb.php');

class ipsController{
    private $db;

    public function __construct(){
        $this->db = new ipsDb(); 
    }

    public function getIpsByOlt($id){
        $result = $this->db->getIpsByOlt($id);

        return $result;
    }
    public function getIpOlt($ip){
        $result = $this->db->getIpOlt($ip);
        return $result;
    }
    public function getIpsTable($id){
        $result = $this->db->getIpsTable($id);

        return $result;
    }
}
