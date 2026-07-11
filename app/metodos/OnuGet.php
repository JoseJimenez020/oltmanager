<?php
require_once (__DIR__ . '..\..\metodos\OnuDb.php');
require_once (__DIR__ . '..\..\class\OidOnu.php');
require_once (__DIR__ . '/oltProfile/oltProfileDb.php');

class OnuGet{

    /**
     * Cache en memoria (por request) de perfiles OLT ya resueltos,
     * para no golpear la DB en cada llamada SNMP dentro de un mismo loop.
     */
    private static $profileCache = [];
    private static $oltDbInstance = null;

    /**
     * Resuelve IP privada + communities de una OLT desde la tabla olts_list,
     * en lugar del arreglo hardcodeado OltManager (app/class/Olt.php).
     * Acepta OltName u OltIdApi, igual que GetSnmpProfile().
     */
    private function getOltProfile($host){
        if (isset(self::$profileCache[$host])) {
            return self::$profileCache[$host];
        }
        $db = $this->getOltDb();
        $perfil = $db->GetSnmpProfile($host);
        self::$profileCache[$host] = $perfil ?: null;
        return self::$profileCache[$host];
    }

    private function getOltDb(){
        if (self::$oltDbInstance === null) {
            self::$oltDbInstance = new oltProfile();
        }
        return self::$oltDbInstance;
    }

    //devuelve array total en formato snmp plain
    public function Data($host='central',$oid="OnuName"){
        $perfil = $this->getOltProfile($host);
        if (!$perfil) {
            error_log("[OnuGet::Data] OLT '$host' no encontrada en olts_list");
            return array("NumeroError" => "OLT_NOT_FOUND");
        }

        $onu = new Onu();
        $onuOid = $onu->onuOid();

        $session = new SNMP(SNMP::VERSION_2c, $perfil['OltIpPrivate'], $perfil['ReadComm']);
        $session->quick_print = 1;

        $result = @$session->walk($onuOid[$oid], true);
        $err = $session->getErrno();
        $session->close();

        if ($err == 0) {
            return $result;
        } else {
            return array("NumeroError" => $err);
        }
    }

    //devuelve array total en formato snmp library
    //$host y $comm normalmente son el mismo valor; se resuelven por separado
    //solo por compatibilidad con firmas antiguas que pudieran diferir.
    public function GetData($host="central",$comm="central",$se=true,$oid="OnuName"){
        $hostPerfil = $this->getOltProfile($host);
        $commPerfil = ($comm === $host) ? $hostPerfil : $this->getOltProfile($comm);

        if (!$hostPerfil || !$commPerfil) {
            error_log("[OnuGet::GetData] OLT '$host' o '$comm' no encontrada en olts_list");
            return array("NumeroError" => "OLT_NOT_FOUND");
        }

        if ($se) {
            $onu = new Onu();
            $oidArr = $onu->onuOid();
        } else {
            require_once (__DIR__ . '..\..\class\OidOlt.php');
            $olt = new Olt();
            $oidArr = $olt->oltOid();
        }

        $session = new SNMP(SNMP::VERSION_2C, $hostPerfil['OltIpPrivate'], $commPerfil['ReadComm']);
        $session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print = 1;
        $session->enum_print = false;

        $result = @$session->walk($oidArr[$oid], true);
        $err = $session->getErrno();
        $session->close();

        if ($err !== 0) {
            error_log("[OnuGet::GetData] Error SNMP en '$host' oid '$oid': errno $err");
            return array("NumeroError" => $err);
        }

        return $result;
    }

    //devuelve array con iteracion en ciclo for
    //el ciclo empieza con Name[0],Model[1],Desc[2],Distance[3],Rx[4],Status[5], Sn[6]
    //Recibe como parametro $a= ZonaDeOlt , $b=Index.Posicion
    public function GetOid($a,$b,$c=true){
        $perfil = $this->getOltProfile($a);
        if (!$perfil) {
            return $c ? array(0,0,0,0,0,0,0,0) : array();
        }

        $onu = new Onu();
        $oid = $onu->onuOid();

        $session = new SNMP(SNMP::VERSION_2C, $perfil['OltIpPrivate'], $perfil['ReadComm']);
        $session->oid_output_format = SNMP_OID_OUTPUT_SUFFIX;
        $session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print = 1;

        $result = @$session->get(array(
            "{$oid['OnuName']}.{$b}", "{$oid['OnuModel']}.{$b}", "{$oid['OnuDesc']}.{$b}",
            "{$oid['OnuDistance']}.{$b}", "{$oid['OnuRxOlt']}.{$b}", "{$oid['OnuStatus']}.{$b}",
            "{$oid['OnuSn']}.{$b}"
        ), true);
        $err = $session->getErrno();
        $session->close();

        if ($err == 0) {
            if ($c) {
                $result = array_values($result);
                $result[6] = $this->ParseSn($result[6]);
                return $result;
            } else {
                return $result;
            }
        } else {
            return array(0,0,0,0,0,0,0,0);
        }
    }

    //obtener info para el diagnostico de tiempo real
    //iteracion empieza [0] Rx [1] Status [2] Distance [3] IpDchp[4]IpTR069
    public function GetDiagOnu($a,$b,$c=true){
        $perfil = $this->getOltProfile($a);
        if (!$perfil) {
            return array(0,0,0,0,0);
        }

        $onu = new Onu();
        $oid = $onu->onuOid();

        $session = new SNMP(SNMP::VERSION_2C, $perfil['OltIpPrivate'], $perfil['ReadComm']);
        $session->quick_print = 1;

        $result = @$session->get(array(
            "{$oid['OnuRxOlt']}.{$b}", "{$oid['OnuStatus']}.{$b}", "{$oid['OnuDistance']}.{$b}",
            "{$oid['OnuIpHostConfig']}.{$b}.1", "{$oid['OnuIpHostConfig']}.{$b}.2"
        ), true);
        $err = $session->getErrno();
        $session->close();

        if ($err == 0) {
            if ($c) {
                return array_values($result);
            } else {
                return $result;
            }
        } else {
            return array(0,0,0,0,0);
        }
    }

    //Recibe como parametro $a= ZonaDeOlt , $b=Index.Posicion
    public function GetOneOid($a,$b,$c){
        $perfil = $this->getOltProfile($a);
        if (!$perfil) {
            return 0;
        }

        $onu = new Onu();
        $oid = $onu->onuOid();

        $session = new SNMP(SNMP::VERSION_2C, $perfil['OltIpPrivate'], $perfil['ReadComm']);
        $session->oid_output_format = SNMP_OID_OUTPUT_SUFFIX;
        $session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print = 1;

        $result = @$session->get("{$oid[$b]}.{$c}", true);
        $err = $session->getErrno();
        $session->close();

        if ($err == 0) {
            return $result;
        } else {
            return 0;
        }
    }

    // (ParseSnNew, ParseSn quedan exactamente igual: no dependen de credenciales)
    public function ParseSnNew($value1){
        $len=strlen($value1);
                if($len == 26 ){
                        $replace=str_replace(' ', '', $value1);
                        $subsVendor=substr($replace,1,8);
                        $vendor=hex2bin($subsVendor);
                        $subsSn=substr($replace,9,-1);
                        $sn=$vendor . $subsSn;
                        return $sn;
                }elseif($len == 11){
                        $vendorPos=strpos($value1, "\\");
                        $subsVendor=substr($value1,1,$vendorPos-1);
                        $subsSn=substr($value1,$vendorPos+1,-1);
                        $strToHex=bin2hex($subsSn);
                        $sn=$subsVendor . $strToHex;
                        $snUpCase=strtoupper($sn);
                        $p=strlen($snUpCase);
                        if($p != 12){
                            $subsErSn=substr($sn,4);
                            $subsOne=substr($subsErSn,0,1);
                            $subsNewSn=substr($sn,5);
                            $subsNewVendor=substr($subsVendor,0,4);
                            $oneToHex=bin2hex($subsOne);
                            $snUpCase=strtoupper($oneToHex . $subsNewSn);
                            $newSn= $subsNewVendor . $snUpCase;
                            if($newSn != 12){
                                $subsFourth=substr($snUpCase,2,1);
                                $subsA=substr($snUpCase,0,2);
                                $subsB=substr($snUpCase,3,);
                                $newHex=bin2hex($subsFourth);
                                $newSn=$subsA . $newHex . $subsB;
                                return $subsNewVendor . $newSn;
                            }
                            return $newSn;
                        }else {
                            return $snUpCase;
                        }
                }elseif ($len != 26) {
                    switch ($len) {
                        case 10:
                            $p=substr($value1,0,1);
                            if($p == '"'){
                            $subsFirts=substr($value1,1,$len-2);
                            $lenFirst=strlen($subsFirts);
                            $vendor=substr($subsFirts,0,$lenFirst-4);
                            $subsSecond=substr($subsFirts,$lenFirst-4);
                            $parseSn=bin2hex($subsSecond);
                            $sn=$vendor . $parseSn;
                            $snUpCase=strtoupper($sn);
                            return $snUpCase;
                            break;
                            }
                        default:
                            return $value1;
                            break;
                    }
                }
    }

    public function ParseSn($value1){
        $value1 = stripslashes($value1);
        $len=strlen($value1);
                if($len == 26 ){
                        $replace=str_replace(' ', '', $value1);
                        $subsVendor=substr($replace,1,8);
                        $vendor=hex2bin($subsVendor);
                        $subsSn=substr($replace,9,-1);
                        $sn=$vendor . $subsSn;
                        return $sn;
                }elseif($len == 11){
                        $vendorPos=strpos($value1, '\\');
                        $subsVendor=substr($value1,1,$vendorPos-1);
                        $subsSn=substr($value1,$vendorPos+1,-1);
                        $strToHex=bin2hex($subsSn);
                        $sn=$subsVendor . $strToHex;
                        $snUpCase=strtoupper($sn);
                        $p=strlen($snUpCase);
                        if($p != 12){
                            $subsErSn=substr($sn,4);
                            $subsOne=substr($subsErSn,0,1);
                            $subsNewSn=substr($sn,5);
                            $subsNewVendor=substr($subsVendor,0,4);
                            $oneToHex=bin2hex($subsOne);
                            $snUpCase=strtoupper($oneToHex . $subsNewSn);
                            $newSn= $subsNewVendor . $snUpCase;
                            if($newSn != 12){
                                $subsFourth=substr($snUpCase,2,1);
                                $subsA=substr($snUpCase,0,2);
                                $subsB=substr($snUpCase,3,);
                                $newHex=bin2hex($subsFourth);
                                $newSn=$subsA . $newHex . $subsB;
                                return $subsNewVendor . $newSn;
                            }
                            return $newSn;
                        }else {
                            return $snUpCase;
                        }
                }elseif ($len != 26) {
                    switch ($len) {
                        case 10:
                            $p=substr($value1,0,1);
                            if($p == '"'){
                            $subsFirts=substr($value1,1,$len-2);
                            $lenFirst=strlen($subsFirts);
                            $vendor=substr($subsFirts,0,$lenFirst-4);
                            $subsSecond=substr($subsFirts,$lenFirst-4);
                            $parseSn=bin2hex($subsSecond);
                            $sn=$vendor . $parseSn;
                            $snUpCase=strtoupper($sn);
                            return $snUpCase;
                            break;
                            }
                        default:
                            return $value1;
                            break;
                    }
                }
    }

    //$a valor de la zonaOlt y $b OID de OnuSnUncf o OnuSn
    public function ParseSnArray($a,$b){
        $var=$this->Data($a,$b);
        $new =  array();
        $index=array();
        $raw=array();
        $merge=array();
        foreach ($var as $key1 => $value1) {
            if ($key1 === "NumeroError") continue; // OLT no encontrada / error SNMP
            $value1 = stripslashes($value1);
            $len=strlen($value1);
            if($len == 26 ){
                    $replace=str_replace(' ', '', $value1);
                    $subsVendor=substr($replace,1,8);
                    $vendor=hex2bin($subsVendor);
                    $subsSn=substr($replace,9,-1);
                    $sn=$vendor . $subsSn;
                    array_push($index,$key1);
                    array_push($new,$sn);
                    array_push($raw,$value1);
            }elseif($len == 11){
                    $vendorPos=strpos($value1, "\\");
                    $subsVendor=substr($value1,1,$vendorPos-1);
                    $subsSn=substr($value1,$vendorPos+1,-1);
                    $strToHex=bin2hex($subsSn);
                    $sn=$subsVendor . $strToHex;
                    $snUpCase=strtoupper($sn);
                    $p=strlen($snUpCase);
                    if($p != 12){
                        $subsErSn=substr($sn,4);
                        $subsOne=substr($subsErSn,0,1);
                        $subsNewSn=substr($sn,5);
                        $subsNewVendor=substr($subsVendor,0,4);
                        $oneToHex=bin2hex($subsOne);
                        $snUpCase=strtoupper($oneToHex . $subsNewSn);
                        $newSn= $subsNewVendor . $snUpCase;
                        array_push($index,$key1);
                        array_push($new,$newSn);
                        array_push($raw,$value1);
                    }else {
                        array_push($index,$key1);
                        array_push($new,$snUpCase);
                        array_push($raw,$value1);
                    }
            }elseif ($len != 26) {
                switch ($len) {
                    case 10:
                        $p=substr($value1,0,1);
                        if($p == '"'){
                        $subsFirts=substr($value1,1,$len-2);
                        $lenFirst=strlen($subsFirts);
                        $vendor=substr($subsFirts,0,$lenFirst-4);
                        $subsSecond=substr($subsFirts,$lenFirst-4);
                        $parseSn=bin2hex($subsSecond);
                        $sn=$vendor . $parseSn;
                        $snUpCase=strtoupper($sn);
                        array_push($index,$key1);
                        array_push($new,$snUpCase);
                        array_push($raw,$value1);
                        break;
                        }
                    default:
                        array_push($index,$key1);
                        array_push($new,$value1);
                        array_push($raw,$value1);
                        break;
                }
            }
        }
        $merge["Sn"] = $new;
        $merge["Index"] = $index;
        $merge["raw"] =$raw;
        return $merge;
    }

    public function GetNextOid($a,$b,$c){
        $perfil = $this->getOltProfile($a);
        if (!$perfil) return null;

        $onu = new Onu();
        $oid = $onu->onuOid();

        $session = new SNMP(SNMP::VERSION_2C, $perfil['OltIpPrivate'], $perfil['ReadComm']);
        $session->valueretrieval = SNMP_VALUE_PLAIN;
        $result = $session->getnext("{$oid['OnuName']}.{$b}.{$c}");
        $session->close();
        return $result;
    }

    //Establece valor {1} para habilitar la onu y valor {2} para desactivar la onu
    //recibe primer valor [$a]=Index.Pos y segundo valor [$b] para establecer valor
    //el tercer valor [$c]=OltZona
    public function SetAdminStateOnu($a,$b,$c){
        $perfil = $this->getOltProfile($c);
        if (!$perfil) {
            error_log("[OnuGet::SetAdminStateOnu] OLT '$c' no encontrada en olts_list");
            return false;
        }

        // OJO: usa community de ESCRITURA, no la de lectura
        $session = new SNMP(SNMP::VERSION_2C, $perfil['OltIpPrivate'], $perfil['WriteComm']);
        //OID zxGponOntAdminState from MIBS ZXGPON-SERVICE
        if ($session->set(".1.3.6.1.4.1.3902.1012.3.28.2.1.1.{$a}", 'i', $b)) {
            return true;
        } else {
            return false;
        }
    }

    //primer valor {$a} OltZona y segundo valor {$b} Index.Pos
    public function GetAdminStateOnu($a,$b){
        $perfil = $this->getOltProfile($a);
        if (!$perfil) return null;

        $session = new SNMP(SNMP::VERSION_2C, $perfil['OltIpPrivate'], $perfil['ReadComm']);
        $session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print = 1;
        //OID zxGponOntAdminState from MIBS ZXGPON-SERVICE
        $result = @$session->get(".1.3.6.1.4.1.3902.1012.3.28.2.1.1.{$b}");
        return $result;
    }

    public function GetTotalOffline(){
        $oltDb = $this->getOltDb();
        $olts  = $oltDb->GetOlt(); // filas de olts_list
        $eonu  = new OnuGet();
        $new   = array();
        $total = 0;

        foreach ($olts as $o) {
            $onu = $eonu->GetData($o['OltName'], $o['OltName'], true, "OnuStatus");
            if (isset($onu['NumeroError'])) continue; // OLT sin respuesta, se omite del conteo

            foreach ($onu as $y1) {
                if ($y1 != 3) {
                    $total += 1;
                    array_push($new, $y1);
                }
            }
        }
        echo $total;
        echo "<br>";
        return $new;
    }

    public function GetLowSignal(){
        $oltDb = $this->getOltDb();
        $olts  = $oltDb->GetOlt();
        $eonu  = new OnuGet();
        $new   = array();
        $warning = 0;
        $critical = 0;

        foreach ($olts as $o) {
            $onu = $eonu->GetData($o['OltName'], $o['OltName'], true, "OnuRxOlt");
            if (isset($onu['NumeroError'])) continue;

            foreach ($onu as $rx) {
                if ($rx <= -30000 && $rx != -80000) {
                    if ($rx > -32000 && $rx != -80000) {
                        array_push($new, $rx);
                        $warning += 1;
                    } elseif ($rx <= -32000 && $rx != -80000) {
                        $critical += 1;
                        array_push($new, $rx);
                    }
                }
            }
        }
        echo "$warning<br>Mayor a -30 y menor a -32<br>";
        echo "$critical<br>Mayor a -32<br>";
    }

    public function GetTemp(){
        $oltDb = $this->getOltDb();
        $olts  = $oltDb->GetOlt();
        $etemp = new OnuGet();
        $new   = array();

        foreach ($olts as $o) {
            $temp = $etemp->GetData($o['OltName'], $o['OltName'], false, "SysTemp");
            if (isset($temp['NumeroError'])) continue;
            foreach ($temp as $tem) {
                array_push($new, $tem);
            }
        }
        return $new;
    }

    public function GetTimeUp(){
        $oltDb = $this->getOltDb();
        $olts  = $oltDb->GetOlt();
        $etemp = new OnuGet();
        $new   = array();

        foreach ($olts as $o) {
            $temp = $etemp->GetData($o['OltName'], $o['OltName'], false, "SysUpTime");
            if (isset($temp['NumeroError'])) continue;
            foreach ($temp as $tem) {
                array_push($new, $tem);
            }
        }
        return $new;
    }

    public function GetSysName(){
        $oltDb = $this->getOltDb();
        $olts  = $oltDb->GetOlt();
        $etemp = new OnuGet();
        $new   = array();

        foreach ($olts as $o) {
            $temp = $etemp->GetData($o['OltName'], $o['OltName'], false, "SysOlt");
            if (isset($temp['NumeroError'])) continue;
            foreach ($temp as $tem) {
                array_push($new, $tem);
            }
        }
        return $new;
    }

    public function GetOne($index,$zona,$oid){
        $eonu = new OnuGet();
        $onu = $eonu->Data($zona,$oid);

        foreach ($onu as $x => $y) {
            if ($x === $index) {
                return $y;
                break;
            }
        }
    }

    //retorna id en tabla speedProfileOlts
    //$key seria nombre olt y $speed nombre oid del profileSpeed
    public function DataSpeedProfile($key,$speed,$direction){
        $eonu = new OnuDb();
        $profile = array('index'=>array(), 'name'=>array());

        $tcont = $this->Data($key,$speed);
        foreach ($tcont as $key1 => $value1) {
            $in = substr($key1,-1);
            if ($in == 1) {
                $tcontOnu = $this->DeleteQuote($value1);
                $id = $eonu->GetIdProfileOlt($tcontOnu,$key,$direction);
                $profile['name'][] = $tcontOnu;
                $profile['index'][] = $id['IdProfile'];
            }
        }
        return $profile;
    }

    //retorna nuevo array con valor sin llave
    public function DataArray($host,$oid){
        $onu = $this->Data($host,$oid);
        $new = array();
        $index = array();
        $value = array();
        foreach ($onu as $x => $y) {
            array_push($index,$x);
            array_push($value,$y);
        }
        set_time_limit(60);
        $new["index"] = $index;
        $new["value"] = $value;
        return $new;
    }

    // ---- A partir de aquí no hay cambios: IndexArray, GetPosIndex, ParseType,
    // CicleType, DeleteQuote, GetVlansOnusV2, GetVlansOnus no dependen de
    // credenciales SNMP, se dejan exactamente igual que en el original. ----

    public function IndexArray($new,$in=true){
        $dot=array();
        if($in){
            return $new;
        }else{
            for ($i=0; $i < count($new) ; $i++) {
                $pos = strpos($new[$i], ".");
                $len=strlen($new[$i]);
                $subs=$pos-$len;
                $rest = substr($new[$i], $subs+1);
                array_push($dot,$rest);
            }
            return $dot;
        }
    }

    public function GetPosIndex($index, $que=true){
        if($que){
            $pos = strpos($index, ".");
            $len=strlen($index);
            $subs=$pos-$len;
            $rest = substr($index, $pos+1);
            return $rest;
        }else{
            $pos = strpos($index, ".");
            $len=strlen($index);
            $subs=$pos-$len;
            $rest = substr($index, 0, $subs);
            return $rest;
        }
    }

    public function ParseType($ty,$retype){
        $sub=substr($ty['OnuTypeName'],0,3);
        if ($sub == 'ZTE' ) {
        $resub=substr($ty['OnuTypeName'],4);
        if ($retype == $resub) {
            return $resub;
        }
        }elseif($retype == $ty['OnuTypeName']) {
        return $ty['OnuTypeName'];
        }
    }

    public function CicleType($type,$typ){
        $re=null;
        foreach ($type as $ty) {
        $re=$this->ParseType($ty,$typ);
            if ($re) {
            return $ty['OnuTypeName'];
            }
        }
        return $typ;
    }

    public function DeleteQuote($name){
        $findString=substr($name,0,1);
        if ($findString === '"') {
        $subs1=substr($name,1);
        $subs2=substr($subs1,0,-1);
        return $subs2;
        }else{
        return $name;
        }
    }
    public function GetVlansOnusV2($onus,$t,$oltVlans){
        $vlansOnus=array(
            'OnuId'=>array(),
            'ServicePortOnu'=>array(),
            'VportOnu'=>array(),
            'VlanOnu'=>array(),
            'AttachedVlan'=>array(),
            'OltVlanId'=>array()
        );
        foreach ($onus as $onu) {
            $indexDb=$onu['IndexOid']. '.'.$onu['OntPos'];
            //echo "Id {$onu['OntId']} IndexOid $index<br>";
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
                            $vlansOnus['OnuId'][]=$onu['OntId'];
                            $vlansOnus['ServicePortOnu'][]=$servicePort;
                            $vlansOnus['VportOnu'][]=$vlanId;
                            $vlansOnus['VlanOnu'][]=$value;
                            $vlansOnus['AttachedVlan'][]='main';
                            foreach ($oltVlans as $oltVlan) {
                                if ($oltVlan['Vlan'] == $value) {
                                    $vlansOnus['OltVlanId'][]=$oltVlan['VlanId'];
                                }
                            }
                            //echo "OnuId {$onu['OntId']} <br>Index $index <br> Pos $pos <br>ServicePort $servicePort <br> Vport $vlanId <br>AttachedVlan main <br>Vlan $value<br>$key <br><br>";
                            $servicePort = 11;
                        }else{
                            $vlansOnus['OnuId'][]=$onu['OntId'];
                            $vlansOnus['ServicePortOnu'][]=$servicePort;
                            $vlansOnus['VportOnu'][]=$vlanId;
                            $vlansOnus['VlanOnu'][]=$value;
                            $vlansOnus['AttachedVlan'][]='minor';
                            foreach ($oltVlans as $oltVlan) {
                                if ($oltVlan['Vlan'] == $value) {
                                    $vlansOnus['OltVlanId'][]=$oltVlan['VlanId'];
                                }
                            }
                            //echo "OnuId {$onu['OntId']} <br>Index $index <br> Pos $pos <br>ServicePort $servicePort <br> Vport $vlanId <br>AttachedVlan minor <br>Vlan $value<br>$key <br><br>";
                            $servicePort++;
                        }
                    }
                    if ($vlanId == 2) {
                            $vlansOnus['OnuId'][]=$onu['OntId'];
                            $vlansOnus['ServicePortOnu'][]=$vlanId;
                            $vlansOnus['VportOnu'][]=$vlanId;
                            $vlansOnus['VlanOnu'][]=$value;
                            $vlansOnus['AttachedVlan'][]='main';
                            foreach ($oltVlans as $oltVlan) {
                                $scope=substr($oltVlan['VlanScope'],0,4);
                                if ($scope == 'mgmt') {
                                    
                                    $vlansOnus['OltVlanId'][]=$oltVlan['VlanId'];
                                }
                            }

                            //echo "OnuId {$onu['OntId']} <br>Index $index <br> Pos $pos <br>ServicePort $vlanId <br> Vport $vlanId <br>AttachedVlan main <br>Vlan $value<br>$key <br><br>";
                    }
                }
            }
            $servicePort=null;
        }
        return $vlansOnus;
    }
    public function GetVlansOnus($onus,$t){
        $vlansOnus=array(
            'OnuId'=>array(),
            'ServicePortOnu'=>array(),
            'VportOnu'=>array(),
            'VlanOnu'=>array(),
            'AttachedVlan'=>array()
        );
        foreach ($onus as $onu) {
            $indexDb=$onu['IndexOid']. '.'.$onu['OntPos'];
            //echo "Id {$onu['OntId']} IndexOid $index<br>";
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
                            $vlansOnus['OnuId'][]=$onu['OntId'];
                            $vlansOnus['ServicePortOnu'][]=$servicePort;
                            $vlansOnus['VportOnu'][]=$vlanId;
                            $vlansOnus['VlanOnu'][]=$value;
                            $vlansOnus['AttachedVlan'][]='main';
                            //echo "OnuId {$onu['OntId']} <br>Index $index <br> Pos $pos <br>ServicePort $servicePort <br> Vport $vlanId <br>AttachedVlan main Vlan $value<br>$key <br><br>";
                            $servicePort = 11;
                        }else{
                            $vlansOnus['OnuId'][]=$onu['OntId'];
                            $vlansOnus['ServicePortOnu'][]=$servicePort;
                            $vlansOnus['VportOnu'][]=$vlanId;
                            $vlansOnus['VlanOnu'][]=$value;
                            $vlansOnus['AttachedVlan'][]='minor';
                            //echo "OnuId {$onu['OntId']} <br>Index $index <br> Pos $pos <br>ServicePort $servicePort <br> Vport $vlanId <br>AttachedVlan minor Vlan $value<br>$key <br><br>";
                            $servicePort++;
                        }
                    }
                    if ($vlanId == 2) {
                            $vlansOnus['OnuId'][]=$onu['OntId'];
                            $vlansOnus['ServicePortOnu'][]=$vlanId;
                            $vlansOnus['VportOnu'][]=$vlanId;
                            $vlansOnus['VlanOnu'][]=$value;
                            $vlansOnus['AttachedVlan'][]='main';
                        //echo "OnuId {$onu['OntId']} <br>Index $index <br> Pos $pos <br>ServicePort $vlanId <br> Vport $vlanId <br>AttachedVlan main Vlan $value<br>$key <br><br>";
                    }
                }
            }
            $servicePort=null;
        }
        return $vlansOnus;
    }
}