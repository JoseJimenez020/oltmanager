<?php
require_once (__DIR__ . '..\..\class\Olt.php');
require_once (__DIR__ . '..\..\metodos\OnuDb.php');
require_once (__DIR__ . '..\..\class\OidOnu.php');

class OnuGet{
    //devuelve array total en formato snmp plain
    public function Data($host='central',$oid="OnuName"){
        $pass = new OltManager();
        $Host= $pass->hostOlt();
        $Comm=$pass->communityOlt();
        $onu =new Onu();
        $onuOid=$onu->onuOid();

        $session = new SNMP(SNMP::VERSION_2c,$Host[$host],$Comm[$host]);
        //$session->exceptions_enabled=SNMP::ERRNO_ANY;
        //$session->oid_output_format = SNMP_OID_OUTPUT_NONE;
        //$session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print=1;
        
        $result = @$session->walk($onuOid[$oid],true);
        $err=$session->getErrno();
        $session->close();

        if($err == 0){
            //$result=array_values($result);
            return $result;
        }else{
            return array("NumeroError" => $err);
        }
        //retorna llave => valor
        
    }
    //devuelve array total en formato snmp library
    public function GetData($host="central",$comm="central",$se=true,$oid="OnuName"){
        $pass = new OltManager();
        $Host= $pass->hostOlt();
        $Comm=$pass->communityOlt();
        $onu =new Onu();
        $onuOid=$onu->onuOid();
        require_once (__DIR__ . '..\..\class\OidOlt.php');
        $olt =new Olt();
        $oltOid=$olt->oltOid();

        $session = new SNMP(SNMP::VERSION_2C,$Host[$host],$Comm[$comm]);
        //$session->oid_output_format = SNMP_OID_OUTPUT_SUFFIX;
        $session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print=1;  
        $session->enum_print = false;
        //$session->oid_increasing_check=false;
        
    
        if($se){
            $result = $session->walk($onuOid[$oid],true);
            //sleep(1);
        }else{
            $result = $session->walk($oltOid[$oid],true);
        }
        
        $session->close();

        return $result;
    }
    //devuelve array con iteracion en ciclo for
    //el ciclo empieza con Name[0],Model[1],Desc[2],Distance[3],Rx[4],Status[5], Sn[6] 
    //Recibe como parametro $a= ZonaDeOlt , $b=Index.Posicion
    public function GetOid($a,$b,$c=true){
        $olt=new OltManager();
        $host=$olt->hostOlt();
        $pass=$olt->communityOlt();
        $onu=new Onu();
        $oid=$onu->onuOid();
        

        $session = new SNMP(SNMP::VERSION_2C, $host[$a], $pass[$a]);
        $session->oid_output_format = SNMP_OID_OUTPUT_SUFFIX;
        $session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print=1;

        $result = @$session->get(array("{$oid['OnuName']}.{$b}","{$oid['OnuModel']}.{$b}","{$oid['OnuDesc']}.{$b}","{$oid['OnuDistance']}.{$b}","{$oid['OnuRxOlt']}.{$b}","{$oid['OnuStatus']}.{$b}","{$oid['OnuSn']}.{$b}"),true);
        $err=$session->getErrno();
        $session->close();
        if($err == 0){
            if($c){
                $result=array_values($result);
                $result[6]=$this->ParseSn($result[6]);
                return $result;
            }else{
                return $result;
            }
            
        }else{
            return array(0,0,0,0,0,0,0,0);
        }
        
    }
    //obtener info para el diagnostico de tiempo real
    //iteracion empieza [0] Rx [1] Status [2] Distance [3] IpDchp[4]IpTR069
    public function GetDiagOnu($a,$b,$c=true){
        $olt=new OltManager();
        $host=$olt->hostOlt();
        $pass=$olt->communityOlt();
        $onu=new Onu();
        $oid=$onu->onuOid();
        

        $session = new SNMP(SNMP::VERSION_2C, $host[$a], $pass[$a]);
        //$session->oid_output_format = SNMP_OID_OUTPUT_SUFFIX;
        //$session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print=1;

        $result = @$session->get(array("{$oid['OnuRxOlt']}.{$b}","{$oid['OnuStatus']}.{$b}","{$oid['OnuDistance']}.{$b}","{$oid['OnuIpHostConfig']}.{$b}.1","{$oid['OnuIpHostConfig']}.{$b}.2"),true);
        $err=$session->getErrno();
        $session->close();
        if($err == 0){
            if($c){
                $result=array_values($result);
                return $result;
            }else{
                return $result;
            }
            
        }else{
            return array(0,0,0,0,0);
        }
        
    }
    //Recibe como parametro $a= ZonaDeOlt , $b=Index.Posicion
    public function GetOneOid($a,$b,$c){
        $olt=new OltManager();
        $host=$olt->hostOlt();
        $pass=$olt->communityOlt();
        $onu=new Onu();
        $oid=$onu->onuOid();
        

        $session = new SNMP(SNMP::VERSION_2C, $host[$a], $pass[$a]);
        $session->oid_output_format = SNMP_OID_OUTPUT_SUFFIX;
        $session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print=1;

        $result = @$session->get("{$oid[$b]}.{$c}",true);
        $err=$session->getErrno();
        $session->close();
        if($err == 0){
            return $result;
            
        }else{
            return 0;
        }
    }
    //recibe valor de Get OID Numero de serie en HEX
    //retorna numero de serie formateado 
    public function ParseSnNew($value1){
        $len=strlen($value1);
                if($len == 26 ){
                        $replace=str_replace(' ', '', $value1);
                        $subsVendor=substr($replace,1,8);
                        $vendor=hex2bin($subsVendor);
                        
                        $subsSn=substr($replace,9,-1);
                        $sn=$vendor . $subsSn;
                        //echo "{$key1} => {$sn}<br>";
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
                            }else{

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
                        //echo "{$key1} => {$sn}<br>";
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
                            }else{

                            }
                            
                        default:
                            return $value1;
                            break;
                    }
                }
    }
    //$a valor de la zonaOlt y $b OID de OnuSnUncf o OnuSn
    //retorna array multidimensional 
    //array["Sn"] retornas numeor de serie
    //array["Index"] retorna el index del numero de serie  
    public function ParseSnArray($a,$b){
        $var=$this->Data($a,$b);
        $new =  array();
        $index=array();
        $raw=array();

        $merge=array();
        foreach ($var as $key1 => $value1) {
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
                    //return $sn;
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
                        }else{

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
        $olt=new OltManager();
        $host=$olt->hostOlt();
        $pass=$olt->communityOlt();
        $onu=new Onu();
        $oid=$onu->onuOid();

        $session = new SNMP(SNMP::VERSION_2C, $host[$a], $pass[$a]);
        $session->valueretrieval = SNMP_VALUE_PLAIN;
        $result = $session->getnext("{$oid['OnuName']}.{$b}.{$c}");
        $session->close();
        return $result;
    }
    //Establece valor {1} para habilitar la onu y valor {2} para desactivar la onu
    //recibe primer valor [$a]=Index.Pos y segundo valor [$b] para establecer valor
    //el tercer valor [$c]=OltZona 
    public function SetAdminStateOnu($a,$b,$c){
        $pass = new OltManager();
        $Host= $pass->hostOlt();
        $Comm=$pass->communityWrite();

        $session = new SNMP(SNMP::VERSION_2C,$Host[$c],$Comm[$c]);
        //OID zxGponOntAdminState from MIBS ZXGPON-SERVICE
        if($session->set(".1.3.6.1.4.1.3902.1012.3.28.2.1.1.{$a}", 'i', $b)){
            return true;
        }else{
            return false;
        }

        
    }
    //primer valor {$a} OltZona y segundo valor {$b} Index.Pos
    public function GetAdminStateOnu($a,$b){
        $pass = new OltManager();
        $Host= $pass->hostOlt();
        $Comm=$pass->communityOlt();

        $session = new SNMP(SNMP::VERSION_2C,$Host[$a],$Comm[$a]);
        $session->valueretrieval = SNMP_VALUE_LIBRARY;
        $session->quick_print=1;
        //$session->enum_print = true; 
        //OID zxGponOntAdminState from MIBS ZXGPON-SERVICE
        $result=@$session->get(".1.3.6.1.4.1.3902.1012.3.28.2.1.1.{$b}");
        return $result;
    }
    public function GetTotalOffline(){
        $eolt=new OltManager();
        $olt=$eolt->hostOlt();
        $eonu=new OnuGet();
        $new=array();

        $total=0;
        foreach($olt as $x =>$y){
            $onu=$eonu->GetData($x,$x,true,"OnuStatus");

            foreach($onu as $x1 => $y1){
                //if($y1== 1 || $y1 == 4 || $y1 == 6){
                if($y1 != 3){
                    $total+=1;
                    array_push($new,$y1);
                }
            }
        }
            echo $total;
            echo "<br>";
            return $new;
    }
    
    public function GetLowSignal(){
        $eolt=new OltManager;
        $olt=$eolt->hostOlt();
        $eonu= new OnuGet();
        $new =array();
        $warning=0;
        $critical=0;
        foreach($olt as $x =>$y){
            $onu=$eonu->GetData($x,$x,true,"OnuRxOlt");
            foreach($onu as $x1 =>$rx){
                if($rx <= -30000 && $rx != -80000){
                    if ($rx > -32000 && $rx != -80000) {
                        array_push($new,$rx);
                        $warning+=1;
                    }elseif ($rx <= -32000 && $rx != -80000) {
                        $critical+=1;
                        array_push($new,$rx);
                    }
                    
                }
            }
        }
        echo"$warning<br>Mayor a -30 y menor a -32<br>";
        echo "$critical<br>Mayor a -32<br>";
    }

    public function GetTemp(){
        $eolt=new OltManager();
        $olt=$eolt->hostOlt();
        $etemp=new OnuGet();
        $new =array();

        foreach($olt as $x => $y){
            $temp=$etemp->GetData($x,$x,false,"SysTemp");
         
            foreach($temp as $x1=>$tem){
                
                array_push($new,$tem);
            }
        }
        return $new;
    }

    public function GetTimeUp(){
        $eolt=new OltManager();
        $olt=$eolt->hostOlt();
        $etemp=new OnuGet();
        $new =array();

        foreach($olt as $x => $y){
            $temp=$etemp->GetData($x,$x,false,"SysUpTime");
           
            foreach($temp as $x1=>$tem){
            
                array_push($new,$tem);
            }
        }
        return $new;
    }

    public function GetSysName(){
        $eolt=new OltManager();
        $olt=$eolt->hostOlt();
        $etemp=new OnuGet();
        $new =array();

        foreach($olt as $x => $y){
            $temp=$etemp->GetData($x,$x,false,"SysOlt");
            
            foreach($temp as $x1=>$tem){
                
                array_push($new,$tem);
            }
        }
        return $new;
    }

    public function GetOne($index,$zona,$oid){
        $eonu=new OnuGet();
        $onu=$eonu->Data($zona,$oid);

        foreach($onu as $x =>$y){
            if($x === $index){
                return $y;
                break;
            }
        }
    }
    //retorna id en tabla speedProfileOlts
    //$key seria nombre olt y $speed nombre oid
    //del profileSpeed
    public function DataSpeedProfile($key,$speed,$direction){
        $eonu= new OnuDb();

        $profile=array(
            'index'=>array(),
            'name'=>array()
        );
            $tcont = $this->Data($key,$speed);
            foreach ($tcont as $key1 => $value1) {
                $in=substr($key1,-1);
                if ($in == 1) {
                $tcontOnu=$this->DeleteQuote($value1);
                $id=$eonu->GetIdProfileOlt($tcontOnu,$key,$direction);
                $profile['name'][]=$tcontOnu;
                $profile['index'][]=$id['IdProfile'];
    
                }
                
            }
        return $profile; 
    }
    //retorna nuevo array con valor sin llave
    public function DataArray($host,$oid){
        
        $onu=$this->Data($host,$oid);
        $new=array();
        $index=array();
        $value=array();
        foreach($onu as $x => $y){
            array_push($index,$x);
            array_push($value,$y);
        }
        set_time_limit(60);
        $new["index"]= $index;
        $new["value"]=$value;
        return $new;
    }
    //retorna nuevo array con valor index 
    public function IndexArray($new,$in=true){
        
        //$onu=$this->Data($host,"OnuName");
        //$new=array();
        $dot=array();
        //foreach($onu as $x => $y){
        //    array_push($new,$x);
        //}
        //retorna nuevo arreglo con valor sin llave
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
        $len = strlen($ty['OnuTypeName']);
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
