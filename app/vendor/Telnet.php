<?php

class OpenSock{
    private $host ='';
    private $user='';
    private $pass='';
    private $errno='';
    private $errstr='';
    private $con = null;
    private $log = '';
    
    function __construct($host =''){
        if( $host!='' ) $this->host  = $host;

        $this->con  = fsockopen($host, 23, $errno, $errstr, 50);
        if( !$this->con ) {
            $this->log .= "Connection failed !"; 
        }
    }
    function open(){
        $this->con  = fsockopen($this->host, 23, $errno, $errstr, 50);
    }
    function close(){
        fclose($this->con);
    }
    function deleteOnu($tarjeta){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-olt_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}\n";
        $salida .= "no onu {$tarjeta['OntPos']}\n";
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);
        

        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        
    }
    function getStatusTel($tarjeta){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "show pon power attenuation gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "show mac gpon onu gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "show gpon onu detail-info gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "exit\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }
    function getMacOnu($tarjeta){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "show mac gpon onu gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "exit\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }

    function getConfigTel($tarjeta){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "show running-config interface gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "show onu running config gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "exit\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }
    function putSpeedProTel($tarjeta){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "tcont 1 profile {$tarjeta['Up']}\n";
        $salida .= "gemport 1 tcont 1\n";
        $salida .= "gemport 1 traffic-limit downstream {$tarjeta['Down']}\n";
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }
    function putDisableTel($tarjeta,$metodo){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        if ($metodo == 'disable') {
            $salida .= "shutdown\n";
        }else {
            $salida .= "no shutdown\n";
        }
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "y\n";
        $salida .= "y\n";
        fwrite($this->con, $salid);

        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }
    
    function putRestoreTel($tarjeta){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "restore factory\n";
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "y\n";
        $salid .= "y\n";
        fwrite($this->con, $salid);
        
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }
    function putResyncTel($onu,$vlan){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$onu['UserTelnet']}\n";
        $salida .= "{$onu['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-olt_1/{$onu['IndexCard']}/{$onu['IndexPort']}\n";
        $salida .= "onu {$onu['OntPos']} type {$onu['OntModelo']} sn {$onu['OnuSn']}\n";
        $salida .= "exit\n";
        $salida .= "interface gpon-onu_1/{$onu['IndexCard']}/{$onu['IndexPort']}:{$onu['OntPos']}\n";
        $salida .= "name {$onu['OntNombre']}\n";
        $salida .= "description {$onu['OntZona']}\n";
        $salida .= "tcont 1 profile {$onu['Up']}\n";
        $salida .= "gemport 1 tcont 1\n";
        $salida .= "gemport 1 traffic-limit downstream {$onu['Down']}\n";
        $salida .= "service-port {$vlan[0]['ServicePortOnu']} vport {$vlan[0]['VportOnu']} user-vlan {$vlan[0]['Vlan']} vlan {$vlan[0]['Vlan']}\n";
        $salida .= "exit\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$onu['IndexCard']}/{$onu['IndexPort']}:{$onu['OntPos']}\n";
        $salida .= "flow 1 switch switch_0/1\n";
        $salida .= "gemport 1 flow 1\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "flow mode 1 tag-filter vlan-filter untag-filter discard\n";
        $salid .= "flow 1 pri 0 vlan {$vlan[0]['Vlan']}\n";
        $salid .= "switchport-bind switch_0/1 veip 1\n";
        $salid .= "switchport-bind switch_0/1 iphost 1\n";
        $salid .= "vlan-filter-mode iphost 1 tag-filter vlan-filter untag-filter discard\n";
        $salid .= "vlan-filter iphost 1 pri 0 vlan {$vlan[0]['Vlan']}\n";
        for ($i=1; $i <=$onu['EthernetPorts'] ; $i++) { 
            $salid .= "dhcp-ip ethuni eth_0/$i from-onu\n";
        }
        $salid .= "security-mgmt 998 state enable mode forward ingress-type lan protocol web https\n";
        $salid .= "security-mgmt 999 state enable ingress-type lan protocol ftp telnet ssh snmp tr069\n";
        $salid .= "end\n";
        $salid .= "exit\n";
        fwrite($this->con, $salid);
        sleep(1);
        $sali = "y\n";
        fwrite($this->con, $sali);
        
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }
    function putInsertTel($usuario,$pass,$tarjeta,$puerto,$pos,$type,$sn,$name,$desc,$up,$down,$vlan,$mode){
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "$usuario\n";
        $salida .= "$pass\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-olt_1/$tarjeta/$puerto\n";
        $salida .= "onu $pos type {$type['OnuTypeName']} sn $sn\n";
        $salida .= "exit\n";
        $salida .= "interface gpon-onu_1/$tarjeta/$puerto:$pos\n";
        $salida .= "name $name\n";
        $salida .= "description $desc\n";
        $salida .= "tcont 1 profile $up\n";
        $salida .= "gemport 1 tcont 1\n";
        $salida .= "gemport 1 traffic-limit downstream $down\n";
        $salida .= "service-port 1 vport 1 user-vlan $vlan vlan $vlan\n";
        $salida .= "exit\n";
        $salida .= "pon-onu-mng gpon-onu_1/$tarjeta/$puerto:$pos\n";
        $salida .= "flow 1 switch switch_0/1\n";
        $salida .= "gemport 1 flow 1\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "flow mode 1 tag-filter vlan-filter untag-filter discard\n";
        $salid .= "flow 1 pri 0 vlan $vlan\n";
        $salid .= "switchport-bind switch_0/1 veip 1\n";
        if ($mode === 'routing') {
        $salid .= "switchport-bind switch_0/1 iphost 1\n";
        $salid .= "vlan-filter-mode iphost 1 tag-filter vlan-filter untag-filter discard\n";
        $salid .= "vlan-filter iphost 1 pri 0 vlan $vlan\n";
        $e = $type['EthernetPorts'];
        for ($i=1; $i <=$e; $i++) { 
            $salid .= "dhcp-ip ethuni eth_0/$i from-onu\n";
        }
        }else {
            for ($i=1; $i <=$type['EthernetPorts'] ; $i++) { 
                $salid .= "vlan port eth_0/$i mode tag vlan $vlan\n";
                $salid .= "dhcp-ip ethuni eth_0/$i from-internet\n";
            }
        }
        $salid .= "security-mgmt 998 state enable mode forward ingress-type lan protocol web https\n";
        $salid .= "security-mgmt 999 state enable ingress-type lan protocol ftp telnet ssh snmp tr069\n";
        if ($mode === 'bridging') {
            for ($i=1; $i <=$type['EthernetPorts'] ; $i++) { 
                $salid .= "loop-detect ethuni eth_0/$i enable\n";
            }
        }
        $salid .= "end\n";
        $salid .= "exit\n";
        fwrite($this->con, $salid);
        sleep(1);
        $sali = "y\n";
        fwrite($this->con, $sali);
        
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        fclose($this->con);
        return $text;
    }
    function putRebootTel($tarjeta){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos
        //con el siguiente formato se envia la confirmacion
        //por comando mediante fsockopen
        //anexando la cadena de comando a enviar y un fwrite
        //en donde se manda un sleep y se vuelve anexar la cadena
        //de comandos para enviar un siguiente fwrite
        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "reboot\n";
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "y\n";
        $salid .= "end\n";
        $salid .= "exit\n";
        fwrite($this->con,$salid);
        sleep(1);
        $sali = "y\n";
        fwrite($this->con,$sali);

        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }
    //funcion para la gestion remota del onu
    function putOnuMgmTel($tarjeta,$vlan,$config){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos
        //con el siguiente formato se envia la confirmacion
        //por comando mediante fsockopen
        //anexando la cadena de comando a enviar y un fwrite
        //en donde se manda un sleep y se vuelve anexar la cadena
        //de comandos para enviar un siguiente fwrite
        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        if ($config == 'dhcp') {
            $salida .= "no switchport-bind iphost 1\n";
        }
        $salida .= "no ip-host 1\n";
        $salida .= "no wan-ip 1\n";
        $salida .= "no wan 1\n";
        $salida .= "no pppoe 1\n";
        $salida .= "no vlan-filter-mode iphost 1\n";
        $salida .= "switchport-bind switch_0/1 iphost 1\n";
        $salida .= "vlan-filter-mode iphost 1 tag-filter vlan-filter untag-filter discard\n";
        $salida .= "vlan-filter iphost 1 pri 0 vlan $vlan\n";
        if ($config == 'dhcp') {
            $salida .= "ip-host 1 dhcp-enable enable ping-response enable traceroute-response enable\n";
        }
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con,$salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        fclose($this->con);
        return $text;
    }
    function putOnuMgmRemoteAccTel($tarjeta,$mgmt,$config){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos
        //con el siguiente formato se envia la confirmacion
        //por comando mediante fsockopen
        //anexando la cadena de comando a enviar y un fwrite
        //en donde se manda un sleep y se vuelve anexar la cadena
        //de comandos para enviar un siguiente fwrite
        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        if ($config == 'default') {
            $salida .= "no security-mgmt $mgmt\n";
        }
        if ($config == 'dhcp' && $mgmt ==1) {
            $salida .= "security-mgmt $mgmt state enable mode forward protocol web https\n";
            $salida .= "security-mgmt $mgmt start-src-ip 0.0.0.0 end-src-ip 0.0.0.0\n";
        }elseif ($config == 'dhcp' && $mgmt  != 1 ) {
            $salida .= "no security-mgmt $mgmt\n";
        }
        if ($config == 'tr069' && $mgmt ==5) {
            $salida .= "security-mgmt $mgmt state enable mode forward protocol web https\n";
            $salida .= "security-mgmt $mgmt start-src-ip 0.0.0.0 end-src-ip 0.0.0.0\n";
        }elseif ($config == 'tr069' && $mgmt  != 5 ) {
            $salida .= "no security-mgmt $mgmt\n";
        }
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con,$salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        fclose($this->con);
        return $text;
    }
    function putOnuMgmtAttVlanTel($tarjeta,$vlanForm,$vlanDb){
        stream_set_timeout($this->con, 1); // 10 segundos
        
        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "no service-port 1\n";
        $salida .= "no service-port 1\n";
        $salida .= "service-port 1 vport 1 user-vlan $vlanForm vlan $vlanForm\n";
        $salida .= "service-port 1 vport 1 user-vlan $vlanDb vlan $vlanDb\n";
        $salida .= "exit\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "no vlan-filter iphost 1 pri 0 vlan $vlanDb\n";
        $salida .= "vlan-filter iphost 1 pri 0 vlan $vlanForm\n";
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con,$salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        fclose($this->con);
        return $text;
    }
    function deleteOnuAttVlanTel($tarjeta,$sp,$vlan){
        stream_set_timeout($this->con, 1); // 10 segundos
        
        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "no service-port $sp\n";
        $salida .= "exit\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "no flow 1 pri 0 vlan $vlan\n";
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con,$salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        fclose($this->con);
        return $text;
    }
    function putOnuAttVlanTel($tarjeta,$sp,$vlan){
        stream_set_timeout($this->con, 1); // 10 segundos
        
        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "no service-port $sp\n";
        $salida .= "service-port $sp vport 1 user-vlan $vlan vlan $vlan\n";
        $salida .= "exit\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "flow 1 pri 0 vlan $vlan\n";
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con,$salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        fclose($this->con);
        return $text;
    }
    //INICIO TR069
    function putTR069ProfileTel($tarjeta,$mgmt,$ip){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos
        
        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "tcont 2 profile SMARTOLT-VOIPMNG-10M\n";
        $salida .= "gemport 2 tcont 2\n";
        $salida .= "gemport 2 traffic-limit downstream SMARTOLT-VOIPMNG-10M\n";
        $salida .= "switchport mode hybrid vport 2\n";
        $salida .= "no service-port 2\n";
        $salida .= "service-port 2 vport 2 user-vlan $mgmt vlan $mgmt\n";
        $salida .= "exit\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "voip protocol sip\n";
        $salida .= "no switchport-bind iphost 2\n";
        $salida .= "no ip-host 2\n";
        $salida .= "no flow 2\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "flow 2 switch switch_0/1\n";
        $salid .= "flow mode 2 tag-filter vlan-filter untag-filter discard\n";
        $salid .= "flow 2 pri 2 vlan $mgmt\n";
        $salid .= "gemport 2 flow 2\n";
        $salid .= "switchport-bind switch_0/1 iphost 2\n";
        $salid .= "ip-host 2 ip {$ip['ipAddress']} mask {$ip['mask']} gateway {$ip['defaultGateway']}\n";
        $salid .= "ip-host 2 primary-dns {$ip['firstDns']} second-dns {$ip['secDns']}\n";
        $salid .= "vlan-filter-mode iphost 2 tag-filter vlan-filter untag-filter discard\n";
        $salid .= "vlan-filter iphost 2 pri 2 vlan $mgmt\n";
        $salid .= "end\n";
        $salid .= "exit\n";
        fwrite($this->con,$salid);
        sleep(1);
        $sali = "y\n";
        fwrite($this->con,$sali);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }
    function deleteTR069MgmtTel($tarjeta){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos
        
        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "interface gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "no tcont 2\n";
        $salida .= "exit\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "no switchport-bind iphost 2\n";
        $salida .= "no ip-host 2\n";
        $salida .= "no flow 2\n";
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con,$salid);
        $st=1;
        $text=array();
        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            //var_dump($stream_meta_data);
            //echo '<br>';
            if ($st>=10) {
            $ch = preg_split('//', $c, -1, PREG_SPLIT_NO_EMPTY);
            $endLine=count($ch)-3;
            if (strlen($c) < 4) continue;
            if ($ch[$endLine] != '#') {
            //var_dump($c);
            //echo '<br>';
            $text[]=$c;
            }
        }
        $st++;
        
        if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        array_splice($text,-1);
        
        return $text;
    }
    //TERMINA TR069
    //inserta el speed de acuerdo al type si es tcont o traffic
    function profileSpeed($usuario,$pass,$name,$speed,$type){
        stream_set_timeout($this->con, 1); // 10 segundos
        //$result = array();
        $salida = "$usuario\n";
        $salida .= "$pass\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "gpon\n";
        if ($type == 'down') {
            $salida .= "profile traffic $name sir $speed pir $speed\n";
        }elseif ($type == 'up') {
            $salida .= "profile tcont $name type 5 fixed 64 assured 64 maximum $speed\n";
        }
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);

        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            //$result[]=$c;
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        fclose($this->con);
        //return $result;
    }
    //elimina de acuerdo al type y nombre del profile
    function noProfileSpeed($usuario,$pass,$name,$type){
        stream_set_timeout($this->con, 1); // 10 segundos
        //$result = array();
        $salida = "$usuario\n";
        $salida .= "$pass\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "gpon\n";
        if ($type == 'down') {
            $salida .= "no profile traffic $name\n";
        }elseif ($type == 'up') {
            $salida .= "no profile tcont $name\n";
        }
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);

        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            //$result[]=$c;
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        fclose($this->con);
        //return $result;
    }
    //insertar una onutype en la olt
    function typeAdd($usuario,$pass,$name,$eth,$wifi,$pots){
        stream_set_timeout($this->con, 1); // 10 segundos
        //$result = array();
        $salida = "$usuario\n";
        $salida .= "$pass\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon\n";
        $salida .= "onu-type $name gpon max-tcont 8\n";
        $salida .= "onu-type $name gpon max-gemport 32\n";
        $salida .= "onu-type $name gpon max-switch-perslot 8\n";
        $salida .= "onu-type $name gpon max-iphost 5\n";
        $salida .= "onu-type $name gpon max-flow-perswitch 8\n";
        for ($i=1; $i <$eth+1 ; $i++) { 
            $salida .= "onu-type-if $name eth_0/$i\n";
        }
        if ($wifi) {
            for ($i=1; $i <$wifi+1 ; $i++) { 
                $salida .= "onu-type-if $name wifi_0/$i\n";
            }
        }
        if ($pots) {
            for ($i=1; $i <$pots+1 ; $i++) { 
                $salida .= "onu-type-if $name pots_0/$i\n";
            }
        }
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);

        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            //$result[]=$c;
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        fclose($this->con);
        //return $result;
    }
    //eliminar una onutype de la olt
    function noTypeAdd($usuario,$pass,$name){
        stream_set_timeout($this->con, 1); // 10 segundos
        $result = array();
        $salida = "$usuario\n";
        $salida .= "$pass\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon\n";
        $salida .= "no onu-type $name\n";
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);

        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $result[]=$c;
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        fclose($this->con);
        return $result;
    }

    function onuWifi($onu,$accion){
        stream_set_timeout($this->con, 1); // 10 segundos
        $result = array();
        $salida = "{$onu['UserTelnet']}\n";
        $salida .= "{$onu['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$onu['IndexCard']}/{$onu['IndexPort']}:{$onu['OntPos']}\n";
        if ($accion == 'enable') {
            $salida .= "wifi enable\n";
        }else {
            $salida .= "wifi disable\n";
        }
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con,$salida);
        sleep(1);
        $salid = "y\n";
        fwrite($this->con, $salid);

        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $result[]=$c;
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        fclose($this->con);
        return $result;
    }
    //INICIA BRIDGING/ROUTING
    function BridgingEthMng($tarjeta,$mode,$vlan=''){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        if ($mode == "bridging") {
            $salida .= "no switchport-bind iphost 1\n";
            $salida .= "no ip-host 1\n";
            $salida .= "no wan-ip 1\n";
            $salida .= "no pppoe 1\n";
            for ($i=1; $i <=$tarjeta['EthernetPorts']; $i++) { 
                $salida .= "vlan port eth_0/$i mode tag vlan $vlan\n";
                $salida .= "dhcp-ip ethuni eth_0/$i from-internet\n";
            }
        }else {
            $salida .= "switchport-bind switch_0/1 iphost 1\n";
            $salida .= "vlan-filter-mode iphost 1 tag-filter vlan-filter untag-filter discard\n";   
            $salida .= "vlan-filter iphost 1 pri 0 vlan $vlan\n";
            for ($i=1; $i <=$tarjeta['EthernetPorts']; $i++) { 
                $salida .= "dhcp-ip ethuni eth_0/$i from-onu\n";
                $salida .= "no vlan port eth_0/$i mode\n";
            }
        }
        fwrite($this->con, $salida);
        sleep(1);
        $salid = "end\n";
        $salid .= "exit\n";
        fwrite($this->con, $salid);
        sleep(1);
        $sali = "y\n";
        fwrite($this->con, $sali);
        

        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        
    }
    function VlanFilterMng($tarjeta,$vlan=''){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        $salida .= "no ip-host 1\n";
        $salida .= "no wan-ip 1\n";
        $salida .= "no wan 1\n";
        $salida .= "no pppoe 1\n";
        $salida .= "no vlan-filter-mode iphost 1\n";
        $salida .= "switchport-bind switch_0/1 iphost 1\n";
        $salida .= "vlan-filter-mode iphost 1 tag-filter vlan-filter untag-filter discard\n";
        $salida .= "vlan-filter iphost 1 pri 0 vlan $vlan\n";
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con, $salida);
        sleep(1);
        $sali = "y\n";
        fwrite($this->con, $sali);
        

        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        
    }
    function DhcpIpVlanPortMng($tarjeta,$mode,$vlan='',$port){
        $this->open();
        stream_set_timeout($this->con, 1); // 10 segundos

        $salida = "{$tarjeta['UserTelnet']}\n";
        $salida .= "{$tarjeta['PassTelnet']}\n";
        $salida .= "terminal length 0\n";
        $salida .= "conf t\n";
        $salida .= "pon-onu-mng gpon-onu_1/{$tarjeta['IndexCard']}/{$tarjeta['IndexPort']}:{$tarjeta['OntPos']}\n";
        if ($mode == 'bridging') {
            $salida .= "dhcp-ip ethuni eth_0/$port from-internet\n";
            $salida .= "no vlan port eth_0/$port mode\n";
            $salida .= "vlan port eth_0/$port mode tag vlan $vlan\n";
        }else {
            $salida .= "dhcp-ip ethuni eth_0/$port from-onu\n";
            $salida .= "no vlan port eth_0/$port mode\n";
        }
        $salida .= "end\n";
        $salida .= "exit\n";
        fwrite($this->con, $salida);
        sleep(1);
        $sali = "y\n";
        fwrite($this->con, $sali);
        

        while (!feof($this->con)) {
            $c = fgets($this->con, 128);
            $stream_meta_data = stream_get_meta_data($this->con); //Added line
            if($stream_meta_data['timed_out'] != false) break; //Added line
        }
        
    }
    //TERMINA BRIDGING/ROUTING
}