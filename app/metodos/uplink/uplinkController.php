<?php
require_once(__DIR__ . '../../snmp/oltSnmp.php');
require_once(__DIR__ . '../../snmp/uplinkPort.php');
class uplinkController
{
    public function uplinkProfilePort($zona)
    {
        $s = new nSnmp($zona, 'read');
        $up = new uplinkPortS($s);
        $name = $up->getWalk('IFNAME');
        $uplink = [];
        foreach ($name as $key => $value) {
            $sub = substr($value, 1, 3);
            if ($sub == 'gei' || $sub == 'xge') {
                $quote = trim($value, '\'"');
                $port = $up->gettUplink($key);
                $alias = trim($port['alias'],'\'"');
                $vlantagged = trim($port['vlantagged'], '\'"');
                $optwave = ($port['optwavelength'] != '2147483647') ? $port['optwavelength'] :'N/A';
                $optwave = ($port['optwavelength'] != '2147483647') ? $port['optwavelength'] :'N/A';
                $opttemp = ($port['opttemp'] != '2147483647') ? $port['opttemp'] : 'N/A';
                $uplink[] = [
                    'index' => $key,
                    'port' => $quote,
                    'speed' => $port['ifspeed'],
                    'mtu' => $port['mtu'],
                    'operstatus' => $this->formatAdminStatus($port['operstatus']),
                    'alias' => $alias,
                    'adminstatus' => $this->formatAdminStatus($port['adminstatus']),
                    'actualduplex' => $port['actualduplex'],
                    'conntype' => $this->formatType($port['conectortype']),
                    'confduplexspeed' => $this->formatNegociation($port['confduplexspeed']),
                    'vlantagged' => $vlantagged,
                    'optwave' => $optwave,
                    'opttemp' => $opttemp
                ];
            }
        }
        if (isset($uplink)) {
            $text = ['status'=>true,'entity'=>$uplink];
        }else{
            $text = ['status'=>false,'entity'=> ''];
        }
        return $text;
    }
    public function formatType($type){
        $ty = [1=>'No configurado',2=>'Auto',3=>'Fibra',4=>'Cobre'];
        $format = (isset($ty[$type])) ? $ty[$type] : 'No reconoce';
        return $format;
    }
    public function formatAdminStatus($s){
        $status = [1=>'Habilitado',2=>'Deshabilitado'];
        $format = (isset($status[$s])) ? $status[$s] :'No reconoce';
        return $format;
    }
    public function formatNegociation($duplex){
        $actual = [1=>'Auto',2=>'Forced 10-Half',3=>'Forced 10-Full',4=>'Forced 100-Half',
                    5=>'Forced 100-Full',6=>'Forced 1G-Full',7=>'Forced 10G-Full'];
        $format = (isset($actual[$duplex])) ? $actual[$duplex] :'No reconoce';
        return $format;
    }
}