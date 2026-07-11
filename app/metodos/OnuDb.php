<?php
require_once(__DIR__ . '../../../db/conn.php');
require_once(__DIR__ . '..\..\metodos\OnuGet.php');
require_once(__DIR__ . '/oltProfile/oltProfileController.php'); // nuevo

class OnuDb extends DbConn
{
    public function UpdateForeigKeyGpon()
    {
        $this->pdo->beginTransaction();
        // Prepare statement
        $producto = $this->pdo->prepare('UPDATE
                                        gpon
                                        SET
                                        IdGpon = ?
                                        WHERE IdOlt = ?');
        $i = 1;
        $g = 321;
        for ($i; $i < 113; $i++) {

            echo "id $i idGpon $g<br>";
            set_time_limit(15);
            $producto->execute([$i, $g]);
            $g++;
        }
        $this->pdo->commit();
    }
    public function DeleteAttachedVlanDb($id)
    {
        $query = "DELETE FROM vlans_onus WHERE IdVlan = $id";

        $result = $this->pdo->prepare($query);
        //$result->execute();
        if ($result->execute()) {
            return "exito";
        } else {
            return 'fallo';
        }
    }
    //insertar una main vlan
    public function CommitInsertMainVlan($onuId, $oltVlanId)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT INTO vlans_onus
                                     (OnuId,ServicePortOnu,VportOnu,AttachedVlan,OltVlanId) 
                                     VALUES (?,?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        $stmt->execute([$onuId, '1', '1', 'main', $oltVlanId]);

        // Commit the data into the database
        $this->pdo->commit();
    }
    //insertar un attachedVlan 
    public function CommitInsertAttachedVlan($onuId, $servicePort, $oltVlanId)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT INTO vlans_onus
                                     (OnuId,ServicePortOnu,VportOnu,AttachedVlan,OltVlanId) 
                                     VALUES (?,?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        $stmt->execute([$onuId, $servicePort, '1', 'minor', $oltVlanId]);

        // Commit the data into the database
        $this->pdo->commit();
    }
    //obtener las vlans de una onu en table vlans_onus
    //se usa en attachedVlan olt telnet
    public function GetVlansOnuInfo($id)
    {
        $query = "SELECT v.IdVlan as IdVlanOnu ,v.OnuId as IdOnu,o.VlanId as IdOltVlan,
                v.ServicePortOnu,v.VportOnu,v.AttachedVlan,o.Vlan,o.VlanScope,ol.OltName
                FROM vlans_onus as v
                LEFT JOIN vlans_olt as o ON v.OltVlanId = o.VlanId
                inner join Olts_list as ol on o.VlanOltId = ol.OltIdApi
                WHERE v.OnuId=$id and VlanScope='internet'
                ORDER BY ServicePortOnu";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll();
        return $fetch;
    }
    //insertar las vlans pasando los paramentros
    public function CommitInsertVlansOnus($onus)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT INTO vlans_onus
                                     (OnuId,ServicePortOnu,VportOnu,AttachedVlan,OltVlanId) 
                                     VALUES (?,?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        for ($i = 0; $i < count($onus['OnuId']); $i++) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$onus['OnuId'][$i], $onus['ServicePortOnu'][$i], $onus['VportOnu'][$i], $onus['AttachedVlan'][$i], $onus['OltVlanId'][$i]]);
        }

        // Commit the data into the database
        $this->pdo->commit();
    }
    //vlans del formulario en views/ont por zona
    //le agregamos true para que lo pueda usar la funcion de insertar vlans
    public function GetOltVlanOnu($zona, $mgmt = true)
    {
        $query = "SELECT v.VlanId,v.VlanOltId,v.Vlan,v.VlanDescription,v.VlanScope
                FROM vlans_olt as v
                LEFT JOIN olts_list as o ON v.VlanOltId = o.OltIdApi
                WHERE  o.OltName = '$zona'
                OR v.VlanOltId = '$zona' ";
        if ($mgmt) {
            $query .= " AND VlanScope='internet'";
        }

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    //obtener vlanOlt por id 
    public function GetVlanOltById($id)
    {
        $query = "SELECT v.VlanId,v.VlanOltId,v.Vlan,v.VlanDescription,v.VlanScope
                FROM vlans_olt as v
                WHERE  v.VlanId = $id";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch();
        return $fetch;
    }
    public function GetOnuGponInfoVlan($zona)
    {
        $query = "SELECT onu.OntId,onu.OntGpon,onu.OntOlt,
                gpon.IndexOid,gpon.IndexCard,gpon.IndexPort,
                onu.OntPos
                FROM (onu 
                INNER JOIN gpon 
                ON onu.OntGpon = gpon.IdOlt )
                WHERE onu.OntOlt='$zona'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    //actualizar SpeedProfile
    public function UpdateOnuSpeed($up, $down, $id)
    {
        $this->pdo->beginTransaction();
        // Prepare statement
        $producto = $this->pdo->prepare('UPDATE onu
                                         SET OntSpeedUp = ?, 
                                         OntSpeedDown = ?  
                                         WHERE OntId = ?');

        $producto->execute([$up, $down, $id]);
        $total = $producto->rowCount();
        if ($total > 0) {
            $this->pdo->commit();
        } else {
            $this->pdo->rollBack();
        }
    }
    //funcion para mostrar en la interfaz de ont con el id
    public function GetOnuInfo($id)
    {
        $query = "SELECT o.OntNombre,OntModelo,o.OnuSn,o.OntZona,o.OntOlt,ol.OltName,o.OntPos,
                g.IndexOid,g.IndexCard,g.IndexPort,
                s.ProfileName as Down,
                u.ProfileName as Up,
                o.OntSpeedDown as IdDown,
                o.OntSpeedUp as IdUp
                FROM onu as o
                INNER JOIN olts_list ol ON ol.OltIdApi = o.OntOlt
                INNER JOIN gpon as g ON o.OntGpon = g.IdOlt
                INNER JOIN speed_profile_olts as s ON o.OntSpeedDown = s.IdProfile
                LEFT JOIN speed_profile_olts as u ON o.OntSpeedUp = u.IdProfile
                WHERE  o.OntId = $id";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch();
        return $fetch;
    }
    //funciones para la actualizacion nueva del status
    public function GetGroupZona()
    {
        $query = "SELECT OntOlt 
                FROM onu
                GROUP BY OntOlt
                HAVING count(1)> 1 
                ORDER BY OntOlt";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function GetOnuIndexDb($zona)
    {
        $query = "SELECT onu.OntId,onu.OntPos,gpon.IndexOid
                FROM (onu 
                INNER JOIN gpon 
                ON onu.OntGpon = gpon.IdOlt )
                WHERE OntOlt='$zona'
                ORDER BY onu.OntOlt";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    //funcion para speed profile
    public function GetIdProfileOlt($index, $zona, $tipo)
    {
        $query = "SELECT IdProfile
                FROM speed_profile_olts 
                WHERE IndexProfile ='$index' 
                AND Zona ='$zona' 
                AND Tipo ='$tipo'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch();
        return $fetch;
    }
    //Inserta el profile speed de la olt
    public function InsertProfileOlt($res, $zona, $tipo)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO speed_profile_olts (IndexProfile,ProfileName,Zona,Tipo) 
                                VALUES (?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        for ($i = 0; $i < count($res['index']); $i++) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$res['index'][$i], $res['name'][$i], $zona, $tipo]);
        }

        // Commit the data into the database
        $this->pdo->commit();
    }
    //sentencia para diagnostic 
    public function GetDiagOnu($zona, $tarjeta, $puerto)
    {
        $query = "SELECT onu.OntId,onu.OntPos,gpon.IndexOid
                FROM (onu 
                INNER JOIN gpon 
                ON onu.OntGpon = gpon.IdOlt )
                WHERE  gpon.IndexCard = '$tarjeta' 
                and gpon.IndexPort = '$puerto' 
                and onu.OntOlt = '$zona'
                ORDER BY ABS(OntPos)";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    //obtener datos especificos para realizar un resync a la ONU
    public function GetResyncOnu($id)
    {
        $query = "SELECT gpon.IndexCard,gpon.IndexPort,onu.OntPos,onu.OntModelo,onu.OnuSn,onu.OntNombre,onu.OntZona 
                FROM (onu INNER JOIN gpon ON onu.OntGpon = gpon.IdOlt )
                WHERE onu.OntId = '$id'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch();
        return $fetch;
    }
    //ingresa datos de una GPON en una consulta a la DB
    //en busca de pos para devolver una que se encuentre disponible
    public function GetPosGpon($tarjeta, $puerto, $zona)
    {
        $end = true;
        $countPos = 1;
        $query = "SELECT o.OnuSn,o.OntPos 
                FROM onu o 
                INNER JOIN gpon g ON g.IdOlt = o.OntGpon 
                WHERE g.IndexCard = '$tarjeta' 
                and g.IndexPort= '$puerto' 
                and o.OntOlt = '$zona'
                ORDER BY ABS(o.OntPos)";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        $last = array_key_last($fetch);

        //$pos = @substr($fetch[$last]['OntPos'],1);
        $pos = @$fetch[$last]['OntPos'];
        for ($i = 0; $i < $pos; $i++) {
            $OnuPos = @$fetch[$i]['OntPos'];
            if (isset($OnuPos)) {
                //$posParse=substr($OnuPos,1);
                if ($OnuPos != $countPos) {
                    //echo  "contador $countPos ciclo $i<br>";
                    //echo $OnuPos ." totalFetch$pos" .'<br>';
                    $end = false;
                    return $countPos;
                }

            }
            $countPos++;
        }
        if ($end) {
            //echo  "contador $countPos ciclo $i<br>";
            //echo $OnuPos .'<br>';
            return $countPos;
        }


        //return $fetch;
    }
    //obtener Id onu por Sn
    public function GetOnuBySn($sn)
    {
        $query = "SELECT OntId FROM onu WHERE OnuSn = '$sn'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch();
        return $fetch;
    }
    public function DeleteOnuVlansDb($id)
    {
        $query = "DELETE FROM vlans_onus WHERE OnuId = $id";

        $result = $this->pdo->prepare($query);
        //$result->execute();
        if ($result->execute()) {
            return "exito";
        } else {
            return 'fallo';
        }
    }
    //elimina datos de una ONU mediante la SN
    //para eliminar la onu se tiene que eliminar las vlans que la tabla
    //vlans_onus
    public function DeleteOnuDb($id)
    {
        $query = "DELETE FROM onu WHERE OntId = $id";

        $result = $this->pdo->prepare($query);
        //$result->execute();
        if ($result->execute()) {
            return "exito";
        } else {
            return 'fallo';
        }
    }
    //obtener variables para el formulario de autorizar onu
    public function GetOnuType()
    {
        $query = "SELECT * FROM onu_type";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);

        return $fetch;
    }
    //obtener speedProfile por id 
    public function GetSpeedProfileById($id)
    {
        $query = "SELECT IdProfile,ProfileName 
                FROM speed_profile_olts 
                WHERE IdProfile = $id";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch();
        return $fetch;
    }
    //obtener speedProfile olt
    public function GetSpeedProfile($direction, $zona)
    {
        $query = "SELECT IdProfile,ProfileName 
                FROM speed_profile_olts 
                WHERE Tipo ='$direction' 
                AND Zona ='$zona'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }

    //variables para formulario autorizar
    public function InsertVlansList($res)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO vlans_olt (VlanIdentity, Vlan, VlanDescription, VlanOltId, VlanScope) 
                                VALUES (?,?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        for ($i = 0; $i < count($res['id']); $i++) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$res['id'][$i], $res['vlan'][$i], $res['description'][$i], $res['olt_id'][$i], $res['scope'][$i]]);
        }

        // Commit the data into the database
        $this->pdo->commit();
    }
    public function InsertOltsList($res)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO olts_list (OltName, OltHardVer, OltIpPublic, OltTelnetPort, OltSnmpPort, OltIdApi) 
                                VALUES (?,?,?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        for ($i = 0; $i < count($res['id']); $i++) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$res['name'][$i], $res['oltHardVer'][$i], $res['oltIp'][$i], $res['oltTelnetPort'][$i], $res['oltSnmpPort'][$i], $res['id'][$i]]);
        }

        // Commit the data into the database
        $this->pdo->commit();
    }
    public function InsertOnuType($res)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO onu_type (OnuTypeName, PonType, Capability, EthernetPorts, WifiPorts, VoipPorts,Catv) 
                                VALUES (?,?,?,?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        for ($i = 0; $i < count($res['name']); $i++) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$res['name'][$i], $res['pon_type'][$i], $res['capability'][$i], $res['ethernet_ports'][$i], $res['wifi_ports'][$i], $res['voip_ports'][$i], $res['catv'][$i]]);
        }

        // Commit the data into the database
        $this->pdo->commit();
    }
    public function InsertSpeedProfile($res)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO speed_profile (ProfileName, ProfileSpeed, ProfileDirection, ProfileType) 
                                VALUES (?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        for ($i = 0; $i < count($res['ProfileName']); $i++) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$res['ProfileName'][$i], $res['ProfileSpeed'][$i], $res['ProfileDirection'][$i], $res['ProfileType'][$i]]);
        }

        // Commit the data into the database
        $this->pdo->commit();
    }
    //termina variables para formulario autorizar
    public function InsertGpon($pass)
    {
        require_once(__DIR__ . '../../../db/conn.php');
        $obj = new OnuGet();
        $index = $obj->GetData($pass, $pass, false);
        $card = 2;
        $port = 1;

        foreach ($index as $x => $y) {
            $que = "INSERT INTO oltmgmt( IndexOid, IndexName, IndexPort, IndexCard) VALUES ('$x','$y','$port','$card')";
            mysqli_query($conn, $que);
            //echo "$x : $card : $port <br>";
            $port++;
            if (substr($y, -2) == "16") {
                $card++;
                $port = 1;
            }
        }

    }
    public function InsertIntGpon($int, $s)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO intgpon (IndexIntGpon,NameIntGpon) 
                                VALUES (?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        $i = 1;
        foreach ($int as $key => $value) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$key, $value]);
            $i += 1;
            if ($i > $s) {
                break;
            }
        }

        // Commit the data into the database
        $this->pdo->commit();
    }
    //obtener id gpon mediante index
    public function GetGponId($card, $port)
    {
        $query = "SELECT IdOlt 
                FROM gpon 
                WHERE IndexCard = $card and IndexPort=$port";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch();
        return $fetch;
    }
    // Obtener una Gpon en db
    public function GetCard($index)
    {

        $pos = strpos($index, ".");
        $len = strlen($index);
        $subs = $pos - $len;
        $rest = substr($index, 0, $subs);
        $query = "SELECT   IdOlt, IndexPort, IndexCard FROM gpon WHERE IndexOid=$rest";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch(PDO::FETCH_ASSOC);
        //$row = mysqli_fetch_array($result, MYSQLI_ASSOC);

        return $fetch;
    }
    //obtener todas las gpon  en db
    public function GetOlmgmt($inde, $val = "IdOlt")
    {
        $id = array();
        $port = array();
        $card = array();

        foreach ($inde as $k => $v) {
            $fetch = $this->GetCard($v);
            foreach ($fetch as $key => $value) {

                if ($key == "IndexPort") {
                    //echo "$value<br>";
                    array_push($port, $value);
                } elseif ($key == "IndexCard") {
                    //echo "$value<br>";
                    array_push($card, $value);
                } elseif ($key == "IdOlt") {
                    //echo "$value<br>";
                    array_push($id, $value);
                }
                $in = null;
            }
        }

        switch ($val) {
            case 'IdOlt':
                return $id;
            case 'IndexPort':
                return $port;
            case 'IndexCard':
                return $card;
        }


    }
    //funcion para actualizar el status y rx
    public function CommitUpdateStatusOnu()
    {
        $obj = new OnuGet();
        $oltCtrl = new oltProfileController();
        $olts = $oltCtrl->GetOlt(); // filas de olts_list, reemplaza el arreglo hardcodeado OltManager

        foreach ($olts as $oltRow) {
            $pass = $oltRow['OltName'];
            $sn = array(
                "index" => array(),
                "sn" => array()
            );
            $rx = array();
            $status = array();
            $onuOid = array('OnuSn', 'OnuRxOlt', 'OnuStatus');
            echo $pass;
            echo "<br>";

            $oltError = false;
            foreach ($onuOid as $oid) {
                $index = $obj->Data($pass, $oid);

                if (isset($index['NumeroError'])) {
                    error_log("[CommitUpdateStatusOnu] OLT '$pass' sin respuesta para oid '$oid': " . $index['NumeroError']);
                    $oltError = true;
                    break; // sin sentido seguir con esta OLT si un oid ya falló
                }

                foreach ($index as $x2 => $y2) {
                    switch ($oid) {
                        case "OnuSn":
                            $parse = $obj->ParseSn($y2);
                            $total = $this->GetOnuSnOne($parse);
                            if ($total["total"] != 1) {
                                echo $parse;
                                echo "<br>";
                                echo $y2;
                                echo "<br>";
                                echo $x2;
                                echo "<br>";
                            }
                            array_push($sn["sn"], $parse);
                            array_push($sn["index"], $x2);
                            break;
                        case "OnuRxOlt":
                            array_push($rx, $y2);
                            break;
                        case "OnuStatus":
                            array_push($status, $y2);
                            break;
                    }
                    set_time_limit(60);
                }
                echo count($sn["sn"]);
                echo "<br>";
                echo count($sn["index"]);
                echo "<br>";
            }

            if ($oltError) {
                unset($sn);
                unset($rx);
                unset($status);
                continue; // no ensucia el status de las ONUs con datos parciales
            }

            $this->UpdateStateOnu($rx, $status, $sn, $pass);
            unset($sn);
            unset($rx);
            unset($status);
        }
    }
    //funcion para actualizar el status y la rx
    public function CommitUpdateStatusOnuV2()
    {
        $eonuget = new OnuGet();

        $sta = array(
            'id' => array(),
            'rx' => array(),
            'status' => array()
        );

        $zonas = $this->GetGroupZona();

        foreach ($zonas as $zona) {
            $dzona = $eonuget->DeleteQuote($zona['OntOlt']);
            $rx = $eonuget->Data($dzona, 'OnuRxOlt');
            $status = $eonuget->Data($dzona, 'OnuStatus');
            //echo $dzona .'<br>';
            $onus = $this->GetOnuIndexDb($dzona);
            foreach ($onus as $onu) {
                $index = $onu['IndexOid'] . '.' . $onu['OntPos'];
                $r = @$rx[$index];
                if (isset($r)) {
                    $sta['id'][] = $onu['OntId'];
                    $sta['rx'][] = $rx[$index];
                    $sta['status'][] = $status[$index];
                }
            }
            if ($dzona == 'central') {
                var_dump($sta);
            }
            //$this->UpdateStateOnuById($sta,$dzona);
            unset($sta);
        }
    }
    //Insertar vlan en table vlans
    public function InsertVlanOnuInfo($onuId, $oltVlanId, $service = '1', $vport = '1', $attached = 'main')
    {
        // Start transaction
        $this->pdo->beginTransaction();
        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO vlans_onus (OnuId,ServicePortOnu,VportOnu,AttachedVlan,OltVlanId) 
                                VALUES (?,?,?,?,?)');
        $stmt->execute([$onuId, $service, $vport, $attached, $oltVlanId]);
        // Commit the data into the database
        $this->pdo->commit();
    }
    //Insertar multiples onus o una onu 
    //Una vez insertado en Db se procede a insertar la vlan que table vlans_onus
    public function InsertOnu($id, $name, $model, $dis, $rx, $status, $sn, $desc, $host, $pos, $up, $down, $many = true)
    {
        $eonuget = new OnuGet();

        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO onu (OntGpon, OntNombre, OntModelo, OntDistancia, OntPotencia, OntStatus,OnuSn, OntZona,OntOlt,OntPos,OntSpeedUp,OntSpeedDown) 
                                VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        if ($many) {
            for ($i = 0; $i < count($name); $i++) {
                // All seven parameters are passed into the execute() in a form of an array
                $stmt->execute([$id[$i], $eonuget->DeleteQuote($name[$i]), $eonuget->DeleteQuote($model[$i]), $dis[$i], $rx[$i], $status[$i], $sn[$i], $eonuget->DeleteQuote($desc[$i]), $host, $pos[$i], $up[$i], $down[$i]]);
            }
        } else {
            $stmt->execute([$id, $name, $model, $dis, $rx, $status, $sn, $desc, $host, $pos, $up, $down]);
        }


        // Commit the data into the database
        $this->pdo->commit();
    }
    //ejecuta la funcion de obtener data from Olt 
    public function CommitInsertOnu()
    {
        $eonuget = new OnuGet();
        $oltCtrl = new oltProfileController();
        $olts = $oltCtrl->GetOlt(); // filas de olts_list, reemplaza el arreglo hardcodeado OltManager

        foreach ($olts as $oltRow) {
            $key = $oltRow['OltName'];

            $name = $eonuget->DataArray($key, "OnuName");

            // Si la OLT no responde SNMP, DataArray() empuja un único elemento
            // con la marca NumeroError en vez de datos reales; se salta esta OLT
            // para no insertar basura ni contaminar los demás arreglos.
            if (count($name["index"]) === 1 && $name["index"][0] === 'NumeroError') {
                error_log("[CommitInsertOnu] OLT '$key' sin respuesta SNMP: " . $name["value"][0]);
                continue;
            }

            $model = $eonuget->DataArray($key, "OnuModel");
            $desc = $eonuget->DataArray($key, "OnuDesc");
            $dis = $eonuget->DataArray($key, "OnuDistance");
            $rx = $eonuget->DataArray($key, "OnuRxOlt");
            $status = $eonuget->DataArray($key, "OnuStatus");
            $sn = $eonuget->ParseSnArray($key, "OnuSn");
            $id = $this->GetOlmgmt($name["index"], "IdOlt");
            $pos = $eonuget->IndexArray($name["index"], false);
            $speedUp = $eonuget->DataSpeedProfile($key, 'TcontPerOnu', 'up');
            $speedDown = $eonuget->DataSpeedProfile($key, 'GemportPerOnu', 'down');

            $this->InsertOnu($id, $name["value"], $model["value"], $dis["value"], $rx["value"], $status["value"], $sn["Sn"], $desc["value"], $key, $pos, $speedUp['index'], $speedDown['index']);
            sleep(1);
            $oltVlans = $this->GetOltVlanOnu($key, false);
            $onus = $this->GetOnuGponInfoVlan($key);
            $vlans = $eonuget->Data($key, 'VlanOnu');
            $vlan = $eonuget->GetVlansOnusV2($onus, $vlans, $oltVlans);
            $this->CommitInsertVlansOnus($vlan);
            sleep(1);
            set_time_limit(120);
        }
    }
    public function CommitUpdateOnu()
    {
        require_once(__DIR__ . '..\..\metodos\OnuGet.php');
        $eonuget = new OnuGet();
        $data = $this->GetOnu();

        for ($i = 0; $i < count($data); $i++) {
            $zona = $data[$i]["OntOlt"];
            $index = $data[$i]["IndexOid"] . $data[$i]["OntPos"];
            $id = $data[$i]["OntId"];
            $get = $eonuget->GetOid($zona, $index);

            $this->UpdateOnu($get, $id);
            //$this->UpdateOnu(3,1,2,$get[6],$id);
            //echo "$zona<br>$get[0]<br>$get[4]<br>$get[5]<br>$get[3]<br>$get[6]<br>";
            //sleep(1);
            set_time_limit(25);
        }
    }
    public function UpdateOnu($get, $id)
    {
        $this->pdo->beginTransaction();
        // Prepare statement
        $producto = $this->pdo->prepare('UPDATE
                                        onu
                                        SET
                                        OntPotencia = ?,
                                        OntStatus = ?
                                        WHERE OnuSn = ?');

        $producto->execute([$get[4], $get[5], $get[6]]);
        $total = $producto->rowCount();
        if ($total > 0) {
            $this->pdo->commit();
            $respuesta = "Exito $id";
            echo ("<script>console.log('PHP: " . $respuesta . "');</script>");
            //header("location: ". __DIR__ ."/?rta=".$respuesta."&color=verde");
        } else {
            $this->pdo->rollBack();
            $respuesta = "Fracaso $id";
            echo ("<script>console.log('PHP: " . $respuesta . "');</script>");
            //header("location: ". __DIR__ ."/?rta=".$respuesta."&color=rojo"); 
        }
    }
    //actualizar status e potencia en Db
    public function UpdateStateOnuById($sta, $p)
    {
        $this->pdo->beginTransaction();
        // Prepare statement
        $producto = $this->pdo->prepare('UPDATE
                                        onu
                                        SET
                                        OntPotencia = ?,
                                        OntStatus = ?
                                        WHERE OntId = ?');
        for ($i = 0; $i < count($sta['id']); $i++) {
            set_time_limit(120);
            $producto->execute([$sta['rx'][$i], $sta['status'][$i], $sta['id'][$i]]);
        }

        $total = $producto->rowCount();
        if ($total > 0) {
            $this->pdo->commit();
            //$respuesta = "Exito $p";
            //echo("<script>console.log('PHP: " . $respuesta . "');</script>");
            //header("location: ". __DIR__ ."/?rta=".$respuesta."&color=verde");
        } else {
            $this->pdo->rollBack();
            //$respuesta = "Fracaso $p";
            //echo("<script>console.log('PHP: " . $respuesta . "');</script>");
            //header("location: ". __DIR__ ."/?rta=".$respuesta."&color=rojo"); 
        }

    }
    public function UpdateStateOnu($potencia, $status, $sn, $p)
    {
        $this->pdo->beginTransaction();
        // Prepare statement
        $producto = $this->pdo->prepare('UPDATE
                                        onu
                                        SET
                                        OntPotencia = ?,
                                        OntStatus = ?
                                        WHERE OnuSn = ?');
        for ($i = 0; $i < count($sn["sn"]); $i++) {
            set_time_limit(15);
            $producto->execute([$potencia[$i], $status[$i], $sn["sn"][$i]]);
        }
        //$producto->execute([$get[4],$get[5],$get[6]]);
        $total = $producto->rowCount();
        if ($total > 0) {
            $this->pdo->commit();
            $respuesta = "Exito $p";
            echo ("<script>console.log('PHP: " . $respuesta . "');</script>");
            //header("location: ". __DIR__ ."/?rta=".$respuesta."&color=verde");
        } else {
            $this->pdo->rollBack();
            $respuesta = "Fracaso $p";
            echo ("<script>console.log('PHP: " . $respuesta . "');</script>");
            //header("location: ". __DIR__ ."/?rta=".$respuesta."&color=rojo"); 
        }

    }
    public function GetOnu()
    {
        $query = "SELECT o.OntNombre,p.Status,p.Potencia,g.IndexCard,g.IndexPort,o.OntZona,ol.OltName,g.IndexOid,o.OntPos,o.OnuSn,p.Date,o.OntId FROM onu o
                INNER JOIN gpon g ON g.IdOlt = o.OntGpon
                INNER JOIN potencia p
                ON p.Onu = o.OntId
                INNER JOIN status s
                ON s.StatusId = p.Status
                INNER JOIN olts_list ol 
                ON ol.OltIdApi = o.OntOlt 
                WHERE o.OntOlt = 6 
                ORDER BY o.OntId";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function GetOnuSnOne($sn)
    {
        $query = "SELECT count(*) as total FROM onu WHERE OnuSn='$sn'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch();
        //$fetch=$result->fetchAll();
        return $fetch;
    }

    public function GetOnuOff()
    {
        $query = "SELECT * FROM onu o
                INNER JOIN olts_list ol
                ON ol.OltIdApi = o.OntOlt 
                INNER JOIN gpon g 
                ON o.OntGpon = g.IdOlt 
                INNER JOIN potencia p 
                ON p.Onu = o.OntId WHERE p.Status IN (1, 4, 6)  ORDER BY o.OntId";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function GetOnuLow()
    {
        $query = "SELECT * FROM onu o 
                INNER JOIN olts_list ol ON ol.OltIdApi = o.OntOlt
                INNER JOIN gpon g ON o.OntGpon = g.IdOlt 
                INNER JOIN potencia p ON o.OntId = p.Onu 
                WHERE p.Potencia <= -30000 AND p.Potencia > -80000  
                ORDER BY o.OntId";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function GetOnuOne($id)
    {
        $query = "SELECT * FROM onu o 
                INNER JOIN gpon g ON o.OntGpon = g.IdOlt
                INNER JOIN potencia p ON o.OntId = p.Onu 
                WHERE o.OntId=$id 
                ORDER BY o.OntId";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function GetTotalOnus()
    {
        $query = "SELECT
        (SELECT COUNT(*) FROM onu WHERE OntSpeedDown = 773) AS total_unconfigured,
	    (SELECT COUNT(*) FROM potencia) AS total_ok,
        (SELECT COUNT(*) FROM potencia WHERE Status = 3) AS total_online,
        (SELECT COUNT(*) FROM potencia WHERE Status IN (1, 4, 6)) AS total_off,
        (SELECT COUNT(*) FROM potencia WHERE Status = 1) AS total_los,
        (SELECT COUNT(*) FROM potencia WHERE Status = 4) AS total_pfail,
        (SELECT COUNT(*) FROM potencia WHERE Status = 6) AS total_offline,
        (SELECT COUNT(*) FROM potencia WHERE Potencia <= -30000 AND Potencia > -80000) AS total_low,
        (SELECT COUNT(*) FROM potencia WHERE Potencia <= -30000 AND Potencia > -32000) AS total_warning,
        (SELECT COUNT(*) FROM potencia WHERE Potencia <= -32000 AND Potencia > -80000) AS total_critical";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    //Obtener tabla de gponstatus
    public function GetGponStatus()
    {
        $query = "SELECT GponZonaStatus,GponStatus FROM gponstatus";
        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    //ejecuta el codigo para insertar la info a la bd
    public function InsertGponStatus($index, $zona, $status, $name)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                 INTO gponstatus (GponIndexStatus,GponZonaStatus,GponStatus,GponName) 
                                 VALUES (?,?,?,?)');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        $i = 1;
        //foreach ($int as $key => $value) {
        for ($i = 0; $i < count($index); $i++) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$index[$i], $zona[$i], $status[$i], $name[$i]]);

        }

        // Commit the data into the database
        $this->pdo->commit();
    }
    //Ejecuta la insercion a la base de datos 
    public function CommitGponStatus()
    {
        $eonuget = new OnuGet();
        $oltCtrl = new oltProfileController();
        $olts = $oltCtrl->GetOlt(); // filas de olts_list, reemplaza el arreglo hardcodeado OltManager

        $index = array();
        $zona = array();
        $status = array();
        $name = array();

        foreach ($olts as $oltRow) {
            $key = $oltRow['OltName'];

            $gpon = $eonuget->Data($key, 'GponStatus');
            $gponName = $eonuget->Data($key, 'GponName');

            if (isset($gpon['NumeroError']) || isset($gponName['NumeroError'])) {
                error_log("[CommitGponStatus] OLT '$key' sin respuesta SNMP, se omite.");
                continue;
            }

            $valueGpon = array_values($gpon);
            $keyGpon = array_keys($gpon);
            $valueGponName = array_values($gponName);

            // Las dos consultas deben tener la misma longitud; si una vino parcial
            // no confiamos en el índice compartido y saltamos esta OLT.
            if (count($valueGpon) !== count($valueGponName)) {
                error_log("[CommitGponStatus] OLT '$key' con GponStatus/GponName desalineados, se omite.");
                continue;
            }

            for ($i = 0; $i < count($valueGpon); $i++) {
                $sub = substr($valueGponName[$i], 1, 3);
                if ($sub == 'gpo') {
                    array_push($index, $keyGpon[$i]);
                    array_push($zona, $key);
                    array_push($status, $valueGpon[$i]);
                    array_push($name, $valueGponName[$i]);
                }
            }
        }
        $this->InsertGponStatus($index, $zona, $status, $name);
    }
    public function GetGponTotalClient($card, $port, $zone)
    {
        $query = "SELECT onu.OntId,onu.OnuSn,onu.OntOlt,gpon.IndexOid,gpon.IndexCard,gpon.IndexPort FROM onu INNER JOIN gpon ON onu.OntGpon = gpon.IdOlt where gpon.IndexCard = $card and gpon.IndexPort = $port and onu.OntOlt = '$zone' ORDER BY onu.OntId";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch();
        return $fetch;
    }
}

