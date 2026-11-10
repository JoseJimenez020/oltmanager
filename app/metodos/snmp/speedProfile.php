<?php
//app/metodos/snmp/speedProfile.php
require_once(__DIR__ . '/oltSnmp.php');

class speedProfileS extends nSnmp{
    public function __construct($host = '',$comm=''){
        // FIX: antes se creaba `new nSnmp($host,$comm)` en una variable local
        // ($open) que nunca se asignaba a $this, así que $this->sesion (heredado
        // de nSnmp) se quedaba en null. getTcont()/getGemport() siempre
        // regresaban ['error'=>'No sesion'] sin que nadie lo notara, y esos
        // "datos" terminaban insertados en speed_profile_olts.
        parent::__construct($host, $comm);
    }
    public function close(){
        // nSnmp::close() no valida sesión nula; evitamos fatal si el perfil
        // SNMP no se pudo resolver (host inválido, OLT inactiva, etc.)
        if ($this->sesion) {
            parent::close();
        }
    }
    public function deleteQuote($var){
        $findString=substr($var,0,1);
        if ($findString === '"') {
        $subs1=substr($var,1);
        $subs2=substr($subs1,0,-1);
        return $subs2;
        }else{
        return $var;
        }
    }
    public function dataArray($up,$down){
        $new=[
            'index'=>array(),
            'value'=>array(),
            'tipo'=>array()
        ];
        foreach ($up as $k => $v) {
            $new['value'][]=$this->deleteQuote($v);
            $new['index'][]=$k;
            $new['tipo'][]='up';
        }
        foreach ($down as $k => $v) {
            $new['value'][]=$this->deleteQuote($v);
            $new['index'][]=$k;
            $new['tipo'][]='down';
        }
        return $new;
    }
    public function getGemport(){
        return parent::walk(self::$gemportOlt);
    }
    public function getTcont(){
        return parent::walk(self::$tcontOlt);
    }
    public function getSpeedProfile(){
        $v=$this->dataArray($this->getTcont(),$this->getGemport());
        return $v;
    }
}