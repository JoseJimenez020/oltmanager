<?php
require_once(__DIR__ . '/vlanProfileDb.php');

final class vlanProfileController{
    private $db;

    public function __construct(){
        $this->db = new vlanProfileDb();
    }
    public function getAllVlansOlt($id){
        $vlan = [];
        if(empty($id)) return $vlan;
        $result = $this->db->getAllVlansOlt($id);
        foreach ($result as $k) {
            $desc = $k['VlanDescription'] ?? '';
            $vlan[] = ['Id'=>$k['VlanId'],'Vlan'=>$k['Vlan'],'Desc'=>$desc,'Scope'=>$k['VlanScope'],'Total'=>$k['Total']];
        }
        return ['status'=>true,'entity'=>$vlan];
    }
    public function getOltVlan($o){
        $result = $this->db->getOltVlan($o);
        return $result;
    }
    public function getVlansOnus($onus,$t,$o){
        $oltVlans = $this->getOltVlan($o);
        $vlansOnus=[];
        foreach ($onus as $onu) {
            if ($onu['OntOlt'] != $o) {
                continue; 
            }
            $indexDb=$onu['IndexOid']. '.'.$onu['OntPos'];
            
            $servicePort=1;
            foreach ($t as $key => $value) {
            $index=substr($key,0,9);
            
            $subs1=substr($key,10);
            $strPos1=strpos($subs1,".");
            $subs2=substr($subs1,$strPos1+1);
            $strPos2=strpos($subs2,".");
        
            $pos=substr($subs1,0,$strPos1);
            $vlanId=substr($subs2,0,$strPos2);
            
            $indexOlt=$index.'.'.$pos;
                if ($indexOlt === $indexDb) {
                    if ($vlanId == 1) {
                        if ($servicePort == 1) {
                            $vlansOnus[]= [
                            'OnuId'=>$onu['OntId'],
                            'ServicePortOnu'=>$servicePort,
                            'VportOnu'=>$vlanId,
                            'VlanOnu'=>$value,
                            'AttachedVlan'=>'main',
                            'Olt'=>$o,
                            'Index'=>$indexOlt
                            ];
                            $lastIndex = array_key_last($vlansOnus);
                            foreach ($oltVlans as $oltVlan) {
                                if ($oltVlan['Vlan'] == $value) {
                                    $vlansOnus[$lastIndex]['OltVlanId'] = $oltVlan['VlanId'];
                                    break;
                                }
                            }
                            $vlansOnus[$lastIndex]['OltVlanId'] = $vlansOnus[$lastIndex]['OltVlanId'] ?? NULL;
                            $servicePort = 11;
                        }else{
                            $vlansOnus[]= [
                            'OnuId'=>$onu['OntId'],
                            'ServicePortOnu'=>$servicePort,
                            'VportOnu'=>$vlanId,
                            'VlanOnu'=>$value,
                            'AttachedVlan'=>'minor',
                            'Olt'=>$o,
                            'Index'=>$indexOlt
                            ];
                            $lastIndex = array_key_last($vlansOnus);
                            foreach ($oltVlans as $oltVlan) {
                                if ($oltVlan['Vlan'] == $value) {
                                    $vlansOnus[$lastIndex]['OltVlanId'] = $oltVlan['VlanId'];
                                    break;
                                }
                            }
                            $vlansOnus[$lastIndex]['OltVlanId'] = $vlansOnus[$lastIndex]['OltVlanId'] ?? NULL;
                            $servicePort++;
                        }
                    }
                    if ($vlanId == 2) {
                        $vlansOnus[]= [
                        'OnuId'=>$onu['OntId'],
                        'ServicePortOnu'=>$vlanId,
                        'VportOnu'=>$vlanId,
                        'VlanOnu'=>$value,
                        'AttachedVlan'=>'main',
                        'Olt'=>$o,
                        'Index'=>$indexOlt
                        ];
                        $lastIndex = array_key_last($vlansOnus);
                        foreach ($oltVlans as $oltVlan) {
                            $scope=substr($oltVlan['VlanScope'],0,4);
                            if ($scope == 'mgmt') {
                                $vlansOnus[$lastIndex]['OltVlanId'] = $oltVlan['VlanId'];
                                break;
                            }
                        }
                        $vlansOnus[$lastIndex]['OltVlanId'] = $vlansOnus[$lastIndex]['OltVlanId'] ?? NULL;
                    }
                }
            }
            $servicePort=null;
        }
        return $vlansOnus;
    }
    public function insertVlanOnu($o){
        $result = $this->db->insertVlanOnu($o);
        return $result;
    }
    public function GetVlansOnuInfo($id){
        $result = $this->db->GetVlansOnuInfo($id);
        return $result;
    }
    //ONU PROFILE TABLE
    public function getVlanProfileTable($id){
        $v=$this->db->GetVlansOnuInfo($id);
        $vlan=$this->getVlansOnu($v);
        return $vlan;
    }
    private function getVlansOnu($vlans){
        $vlan="";
        foreach ($vlans as $vla) {
            if ($vla['VportOnu'] == 1) {
                $vlan .= "-{$vla['Vlan']}- ";
            } 
        }
        return $vlan;
    }
    public function GetVlanForm($o){
        $result = $this->db->GetVlanForm($o);
        return $result;
    }
    public function getVlansFormById($id){
        $result = $this->db->getVlansFormById($id);
        return $result;
    }
    public function insertVlanOlt($data){
        if(!is_array($data))return ['status'=>false,'error'=>'No informacion'];
        foreach ($data as $k) {
            if(empty($k)) return ['status'=>false,'entity'=>'','error'=>'Campo vacio'];
        }
        return $this->db->insertVlanOlt($data);
    }
    public function deleteVlanOlt($data){
        if(!is_array($data))return ['status'=>false,'error'=> 'Formato incorrecto'];
        foreach ($data as $k) {
            if(empty($k)) return ['status'=>false,'error'=> 'Sin informacion'];
        }
        return $this->db->deleteVlanOlt($data);
    }
}