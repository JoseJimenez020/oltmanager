<?php
require_once(__DIR__ . '/gpon.php');

class gponController{
    private $db;

    public function __construct(){
        $this->db = new Gpon();
    }
    public function getGpon(){
        $result = $this->db->getGpon();
        return $result;
    }
}
