<?php
require_once(__DIR__ . '/oltSnmp.php');

class speedProfileS extends nSnmp{
    public function __construct($host = '',$comm=''){
        if ($host!='') $open = new nSnmp($host,$comm);
    }
    public function close(){
        parent::close();
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
