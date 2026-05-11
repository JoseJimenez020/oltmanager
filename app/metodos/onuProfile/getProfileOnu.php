<?php
require_once(__DIR__ . '../../snmp/profileOnu.php');

class getProfileOnu{
    private $s;
    
    public function __construct(nSnmp $snmp){
        if(isset($snmp)) $this->s = new profileOnuS($snmp);
    }
    public function getProfileOnu($d){
        return self::existValue($this->s->getOnuProfile($d));
    }
    private function existValue($var){
        if(array_key_exists('error', $var)) return $var=[0,0,0,0,'0.0.0.0'];
        $var = array_values($var);
        $var=['rx'=>$var[0],'sta'=>$var[1],'dis'=>$var[2],'admin'=>$var[3],'dhcp'=>$var[4],'tr069'=>$var[5]];
        if ($var['tr069'] != '0.0.0.0') {
            unset($var['dhcp']);
        }else {
            unset($var['tr069']);
        }
        $var = array_values($var);
        return $var;
    }
}