<?php
require_once(__DIR__ . '../../snmp/oltSnmp.php');
require_once(__DIR__ . '../../snmp/statusOnu.php');
require_once(__DIR__ . '/potenciaController.php');
require_once(__DIR__ . '../../onuProfile/profileOnu.php');
class statusOnu{
    private $potencia;
    private $profile;

    public function __construct(){
        $this->potencia = new potenciaController();
        $this->profile = new profileOnu();
    }

    public function updateStatus($status){
        $format = $this->getStatus($status);
        if(empty($format)) return null;
        $update = $this->potencia->updateStatus($format);
        return $update;
    }
    public function getProfileStatus($zona){
        $snmp= new nSnmp($zona,'read');
        $s = new statusOnuS($snmp);
        $format = $s->getProfileStatus();
        return $format;
    }
    public function gettProfile($onu,$zona): ?array{
        if(empty($onu)) return null;
        $snmp= new nSnmp($zona,'read');
        $s = new statusOnuS($snmp);
        return $s->gettProfile($onu);
    }
    public function getStatus($onu){
        if(empty($onu)) return null;
        date_default_timezone_set('America/Merida');
        $s=[];
        $i=$this->profile->olt->getIndexOlt();
        $date= date("Y-m-d H:i:s");
        
        foreach ($onu as $o) {
            $in = @$i[$o['sn']];
            if(empty($in)) continue;
            if ($in['Status'] != $o['status']) {
                $s[]=[
                    'id'=> $in['OntId'],
                    'rx'=> $o['rx'],
                    'status'=> $o['status'],
                    'date'=> $date
                ];
            }
        }
        return $s;
    }
    public function formatOnu($onu){
        if(empty($onu)) return null;
        $this->profile->setGponOnu();
        return $this->profile->formatOnu($onu);
    }
}