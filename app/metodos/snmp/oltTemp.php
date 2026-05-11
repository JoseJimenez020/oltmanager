<?php
require_once(__DIR__ . '/oltSnmp.php');

class oltTempS{
    protected $sesion;

    public function __construct($host = '',$comm = ''){
        if($host!='') $this->sesion = new nSnmp($host,$comm);
    }
    public function getOltTemp(){
        return $this->sesion->get($this->sesion::$oltTemp);
    }
    
    public function close(){
        $this->sesion->close();
    }
}