<?php
class ZTE{
    private $host = 'host';
    private $user = 'user';
    private $port = '22';
    private $password = 'password';
    private $con = null;
    private $shell_type = 'xterm';
    private $shell = null;
    private $log = '';

  function __construct($host='', $port=''  ) {

     if( $host!='' ) $this->host  = $host;
     if( $port!='' ) $this->port  = $port;

     $this->con  = ssh2_connect($this->host, $this->port);
     if( !$this->con ) {
       $this->log .= "Connection failed !"; 
     }

  }

  function authPassword( $user = '', $password = '' ) {

     if( $user!='' ) $this->user  = $user;
     if( $password!='' ) $this->password  = $password;

     if( !ssh2_auth_password( $this->con, $this->user, $this->password ) ) {
       $this->log .= "Authorization failed !"; 
     }

  }

  function openShell( $shell_type = '' ) {

        if ( $shell_type != '' ) $this->shell_type = $shell_type;
    $this->shell = ssh2_shell( $this->con,  $this->shell_type );
    if( !$this->shell ) $this->log .= " Shell connection failed !";

  }

  function autorizarOnu($tarjeta,$puerto,$pos,$tipo,$ns,$nombre,$desc,$vlan,$down,$up) {
        $command = 'terminal length 0';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'conf t';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "interface gpon-olt_1/{$tarjeta}/{$puerto}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "onu {$pos} type {$tipo} sn {$ns}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'exit';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "interface gpon-onu_1/{$tarjeta}/{$puerto}:{$pos}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "name {$nombre}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "description {$desc}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "tcont 1 profile SMARTOLT-{$up}-UP";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'gemport 1 tcont 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "gemport 1 traffic-limit downstream SMARTOLT-{$down}-DOWN";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "service-port 1 vport 1 user-vlan {$vlan} vlan {$vlan}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'exit';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);

        $command = "pon-onu-mng gpon-onu_1/{$tarjeta}/{$puerto}:{$pos}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'flow 1 switch switch_0/1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'gemport 1 flow 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'flow mode 1 tag-filter vlan-filter untag-filter discard';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "flow 1 pri 0 vlan {$vlan}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'switchport-bind switch_0/1 veip 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'switchport-bind switch_0/1 iphost 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'vlan-filter-mode iphost 1 tag-filter vlan-filter untag-filter discard';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "vlan-filter iphost 1 pri 0 vlan {$vlan}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'dhcp-ip ethuni eth_0/1 from-onu';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'dhcp-ip ethuni eth_0/2 from-onu';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'dhcp-ip ethuni eth_0/3 from-onu';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'dhcp-ip ethuni eth_0/4 from-onu';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'dhcp-ip ethuni eth_0/5 from-onu';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'security-mgmt 998 state enable mode forward ingress-type lan protocol web https';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'security-mgmt 999 state enable ingress-type lan protocol ftp telnet ssh snmp tr069';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'end';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'exit';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'y';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
  }

  function autorizarEG8041V5($tarjeta,$puerto,$pos,$tipo,$ns,$nombre,$desc,$vlan,$down,$up) {
    $command = 'terminal length 0';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'conf t';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "interface gpon-olt_1/{$tarjeta}/{$puerto}";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "onu {$pos} type {$tipo} sn {$ns}";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'exit';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "interface gpon-onu_1/{$tarjeta}/{$puerto}:{$pos}";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "name {$nombre}";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "description {$desc}";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "tcont 1 profile SMARTOLT-{$up}-UP";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'gemport 1 tcont 1';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "gemport 1 traffic-limit downstream SMARTOLT-{$down}-DOWN";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "service-port 1 vport 1 user-vlan {$vlan} vlan {$vlan}";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'exit';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);

    $command = "pon-onu-mng gpon-onu_1/{$tarjeta}/{$puerto}:{$pos}";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'flow 1 switch switch_0/1';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'gemport 1 flow 1';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'flow mode 1 tag-filter vlan-filter untag-filter discard';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "flow 1 pri 0 vlan {$vlan}";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'switchport-bind switch_0/1 veip 1';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'switchport-bind switch_0/1 iphost 1';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'vlan-filter-mode iphost 1 tag-filter vlan-filter untag-filter discard';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = "vlan-filter iphost 1 pri 0 vlan {$vlan}";
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'dhcp-ip ethuni eth_0/1 from-onu';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'dhcp-ip ethuni eth_0/2 from-onu';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'dhcp-ip ethuni eth_0/3 from-onu';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'dhcp-ip ethuni eth_0/4 from-onu';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'dhcp-ip ethuni eth_0/5 from-onu';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'security-mgmt 998 state enable mode forward ingress-type lan protocol web https';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'security-mgmt 999 state enable ingress-type lan protocol ftp telnet ssh snmp tr069';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'end';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'exit';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
    $command = 'y';
    fwrite($this->shell,$command .PHP_EOL);
    sleep(1);
  }

  function updateModeDhcp($tarjeta,$puerto,$pos){
        $command = 'terminal length 0';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'conf t';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "pon-onu-mng gpon-onu_1/{$tarjeta}/{$puerto}:{$pos}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'security-mgmt 1 state enable mode forward protocol web https';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'security-mgmt 1 start-src-ip 0.0.0.0 end-src-ip 0.0.0.0';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'no security-mgmt 2';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'no security-mgmt 3';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'no security-mgmt 4';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'end';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'exit';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'y';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
  }

  function updateModeUpdate($tarjeta,$puerto,$pos){
        $command = 'terminal length 0';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'conf t';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "pon-onu-mng gpon-onu_1/{$tarjeta}/{$puerto}:{$pos}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'no switchport-bind iphost 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'no ip-host 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'no wan 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'no wan-ip 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'no pppoe 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'no vlan-filter-mode iphost 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'switchport-bind switch_0/1 iphost 1';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'vlan-filter-mode iphost 1 tag-filter vlan-filter untag-filter discard';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'vlan-filter iphost 1 pri 0 vlan 501';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'ip-host 1 dhcp-enable enable ping-response enable traceroute-response enable';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'end';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'exit';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'y';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
  }

  function deleteOnu($tarjeta,$puerto,$pos){
        $command = 'terminal length 0';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'conf t';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "interface gpon-olt_1/{$tarjeta}/{$puerto}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = "no onu {$pos}";
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'end';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'exit';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
        $command = 'y';
        fwrite($this->shell,$command .PHP_EOL);
        sleep(1);
  }

  function cmdExec( ) {

        $argc = func_num_args();
        $argv = func_get_args();

    $cmd = '';
    for( $i=0; $i<$argc ; $i++) {
        if( $i != ($argc-1) ) {
          $cmd .= $argv[$i]." && ";
        }else{
          $cmd .= $argv[$i];
        }
    }
    echo $cmd;

        $stream = ssh2_exec( $this->con, $cmd );
    stream_set_blocking( $stream, true );
    return fread( $stream, 4096 );

  }

  function getLog() {

     return $this->log; 

  }
}