<?php
require_once(__DIR__ . '/onuTypeDb.php');
require_once(__DIR__ . '../../../../app/vendor/Telnet.php');
class onuTypeController{
    private $db;
    private $tel;
    private $user;
    private $pass;
    public function __construct($ip= '',$user ='',$pass=''){
        $this->db = new onuType();
        if( $ip!='' ) $this->tel = new OpenSock($ip);
        if( $user!='' ) $this->user  = $user;
        if( $pass!='' ) $this->pass  = $pass;
    }

    public function GetOnuType(){
        $type =$this->db->GetOnuType();
        return $type;
    }

    public function InsertType($name,$pon,$cap,$eth,$wifi,$pots){
        $result = $this->db->InsertOnuType($name,$pon,$cap,$eth,$wifi,$pots);
        
        if ($result) {
            return "insertado";
        }else{
            return "error";
        }
    }
    public function typeAdd($name,$eth,$wifi,$pots){
        $this->tel->typeAdd($this->user,$this->pass,$name,$eth,$wifi,$pots);
    }
    public function noTypeAdd($name){
        $this->tel->noTypeAdd($this->user,$this->pass,$name);
    }
    public function GetOne($id){
        $result = $this->db->GetOne($id);
        return $result;
    }

    public function DeleteOne($id){
        $result = $this->db->DeleteOne($id);
        return $result;
    }
}