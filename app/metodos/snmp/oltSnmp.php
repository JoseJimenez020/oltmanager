<?php
require_once(__DIR__ . '../../oltProfile/oltProfileDb.php');
class nSnmp{
    protected $sesion;
    private $db;
    public static $max_rep = 20;
    public static $suffix = true;
    
    public static $oltTemp = ["SysTemp"=>"1.3.6.1.4.1.3902.1015.2.1.3.2",
                              "SysUpTime"=>".1.3.6.1.2.1.1.3",
                              "SysOlt"=>".1.3.6.1.2.1.1.5"];
    protected static $tcontOlt='.1.3.6.1.4.1.3902.1012.3.26.1.1.2';
    protected static $gemportOlt = '.1.3.6.1.4.1.3902.1012.3.26.2.1.2';
    
    protected static $rxGpon = '.1.3.6.1.4.1.3902.1012.3.50.12.1.1.10';

    public function __construct($host= '',$comm=''){
        $this->db = new oltProfile();
        if( $host!='' ) $profile=$this->db->GetSnmpProfile($host);
        if($comm == 'read'){
            $this->sesion= new SNMP(SNMP::VERSION_2c,$profile['OltIpPrivate'],$profile['ReadComm'], 1000000, 3);
        }elseif ($comm =='write') {
            $this->sesion= new SNMP(SNMP::VERSION_2c,$profile['OltIpPrivate'],$profile['WriteComm'], 1000000, 3);
        }
        
    }
    public function close(){
        $this->sesion->close();
    }
    public function walk($var){
        if (!$this->sesion) return ['error'=>'No sesion'];
        $this->sesion->quick_print=1;
        $r= @$this->sesion->walk($var,self::$suffix,self::$max_rep);
        
        if ($this->sesion->getErrno() !== 0) {
            return ['error'=> $this->sesion->getErrno()];
        }
        return $r ?: ['error'=>'No respuesta'];
    }
    public function get($var){
        if (!$this->sesion) return ['error'=>'No sesion'];
        $var = $this->addDotGetTemp($var);
        $this->sesion->quick_print=1;

        $result = @$this->sesion->get($var,self::$suffix);
        if($this->sesion->getErrno() !== 0) return ['error'=> $this->sesion->getErrno()];
        return $result ?: ['error'=>'No respuesta'];        
    }
    public function gett($var){
        if(!$this->sesion) return ['error'=>'No sesion'];

        $this->sesion->quick_print=1;

        $result = @$this->sesion->get($var,self::$suffix);
        if($this->sesion->getErrno() !== 0) return ['error'=>$this->sesion->getErrno()];

        return $result ?: ['error'=> 'No respuesta'];
    }
    public function set($oid){
        return $this->sesion->set("$oid.285283074",'U',22);
    }
    private function addDotGetTemp($oids){
        $result=array();
        foreach ($oids as $key => $value) {
            $result[]=$value . '.0';
        }
        return $result;
    }
    public function __destruct(){
        if($this->sesion) {
            $this->sesion->close();
        }
    }
}