<?php
require_once(__DIR__ . '../../../../db/conn.php');

class onuProfile extends DbConn
{
    public function GetOnus()
    {
        $query = "SELECT * FROM onu";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll();
        return $fetch;
    }
    //ONU PROFILE TABLE 
    public function getOnuProfileTable($id)
    {
        $query = "SELECT 	g.IndexCard,g.IndexPort,
                        o.OntPos,o.OntModelo,o.OnuSn,o.OntNombre,o.OntZona,ol.OltName,
                        s.ProfileName as Down,u.ProfileName as Up
                FROM onu as o
                INNER JOIN olts_list ol ON ol.OltIdApi = o.OntOlt
                INNER JOIN gpon g ON g.IdOlt = o.OntGpon
                LEFT JOIN speed_profile_olts s ON s.IdProfile = o.OntSpeedDown
                LEFT JOIN speed_profile_olts u ON u.IdProfile = o.OntSpeedUp
                WHERE o.OntId = '$id'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function getOnuBySn($s)
    {
        $query = "SELECT o.OntId,g.IndexCard,g.IndexPort,o.OntPos,o.OntOlt,o.OnuSn,
                d.ProfileName as Down,u.ProfileName as Up 
                FROM onu o 
                INNER JOIN gpon g
                ON g.IdOlt = o.OntGpon
                LEFT JOIN speed_profile_olts d 
                ON o.OntSpeedDown = d.IdProfile
                LEFT JOIN speed_profile_olts  u 
                ON o.OntSpeedUp = u.IdProfile
                WHERE o.OnuSn = '$s'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function GetOnu($id)
    {
        $query = "SELECT ol.OltName,
                        g.IndexOid,g.IndexCard,g.IndexPort,
                        o.OntPos,o.OntModelo,o.OnuSn,o.OntNombre,o.OntZona,o.OntOlt,
                        s.ProfileName as Down,u.ProfileName as Up,
                        p.Potencia,p.Status,p.Distancia,p.Date
                FROM onu as o
                INNER JOIN potencia p ON p.Onu = o.OntId
                INNER JOIN vlans_onus v ON v.OnuId = o.OntId 
                INNER JOIN vlans_olt vo ON vo.VlanId = v.OltVlanId
                INNER JOIN olts_list ol ON ol.OltIdApi = vo.VlanOltId
                INNER JOIN gpon g ON g.IdOlt = o.OntGpon
                LEFT JOIN speed_profile_olts s ON s.IdProfile = o.OntSpeedDown
                LEFT JOIN speed_profile_olts u ON u.IdProfile = o.OntSpeedUp
                JOIN onu_type ot 
				ON (
				CONVERT(o.OntModelo USING utf8mb4) COLLATE utf8mb4_general_ci = 
				CONVERT(ot.OnuTypeName USING utf8mb4) COLLATE utf8mb4_general_ci
				)
                WHERE o.OntId = $id";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function getOnuSnId($zona)
    {

        $query = "SELECT o.OnuSn,g.IndexOid,o.OntPos 
            FROM onu o
            INNER JOIN gpon g on g.IdOlt = o.OntGpon
            WHERE o.OntOlt = '$zona'";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute();

        $result = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);

        return $result;
    }
    public function DeleteOnu($id)
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
    public function DeleteBandWith($id)
    {
        $query = "DELETE FROM band_with WHERE IdOnu = $id";

        $result = $this->pdo->prepare($query);
        //$result->execute();
        if ($result->execute()) {
            return "exito";
        } else {
            return 'fallo';
        }
    }
    public function DeleteVlan($id)
    {
        $query = "DELETE FROM vlans_onus WHERE Onuid = $id";

        $result = $this->pdo->prepare($query);
        //$result->execute();
        if ($result->execute()) {
            return "exito";
        } else {
            return 'fallo';
        }
    }
    public function DeletePotencia($id)
    {
        $query = "DELETE FROM potencia WHERE Onu = $id";

        $result = $this->pdo->prepare($query);
        //$result->execute();
        if ($result->execute()) {
            return "exito";
        } else {
            return 'fallo';
        }
    }
    //INICIA INSERT ONU
    public function insertOnus($onu)
    {
        try {
        
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO onu (OntGpon, OntNombre, OntModelo,OnuSn, OntZona,OntOlt,OntPos,OntSpeedUp,OntSpeedDown) 
                                VALUES (?,?,?,?,?,?,?,?,?)');
        $totalUpdates = 0;
        //for ($i = 0; $i < count($p); $i++) {
        foreach ($onu as $p){
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$p['gpon'], $p['name'], $p['model'], $p['sn'], $p['desc'], $p['olt'], $p['pos'], $p['tcont'], $p['gemport']]);
            $totalUpdates += $stmt->rowCount();
        }

        if ($totalUpdates > 0) {
            return $this->pdo->commit();
        } else {
            return $this->pdo->rollBack();
        }
        } catch (Exception $e) {
            return $this->pdo->rollBack(); 
        }
    }
    public function insertOnu($p, $olt)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 

                                INTO onu (OntGpon, OntNombre, OntModelo, OnuSn, OntZona,OntOlt,OntPos,OntSpeedUp,OntSpeedDown) 
                                VALUES (?,?,?,?,?,?,?,?,?)');

        $stmt->execute([$p['gpon'], $p['name'], $p['model'], $p['sn'], $p['desc'], $olt, $p['pos'], $p['tcont'], $p['gemport']]);


        $this->pdo->commit();
    }
    public function getCard()
    {
        $query = "SELECT g.IndexOid,g.IdOlt
                FROM gpon g
                INNER JOIN intgpon ig
                ON g.IdGpon = ig.IdIntGpon";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
        return $fetch;
    }
    public function getOnuGponInfoVlan()
    {
        $query = "SELECT o.OnuSn,o.OntId,o.OntGpon,o.OntOlt,
                g.IndexOid,g.IndexCard,g.IndexPort,
                o.OntPos
                FROM onu o
                INNER JOIN gpon g
                ON o.OntGpon = g.IdOlt
                INNER JOIN olts_list ol
                ON ol.OltIdApi = o.OntOlt";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        return $fetch;
    }
    //TERMINA INSERT ONU
    //INICIA RESYNC ONU
    public function GetResyncOnu($id)
    {
        $query = "SELECT ol.OltIdApi,ol.OltName,
                    g.IndexOid,g.IndexCard,g.IndexPort,
                    o.OntPos,o.OntModelo,o.OnuSn,o.OntNombre,o.OntZona,o.OntOlt,o.OntSpeedUp,o.OntSpeedDown,
                    ot.EthernetPorts,ot.WifiPorts,ot.VoipPorts,
                    s.ProfileName as Down,u.ProfileName as Up,
                    ol.OltIpPrivate,ol.UserTelnet,ol.PassTelnet
                FROM onu as o
                INNER JOIN vlans_onus as v ON o.OntId = v.OnuId
                INNER JOIN vlans_olt as vo ON vo.VlanId = v.OltVlanId
                INNER JOIN olts_list as ol ON ol.OltIdApi = vo.VlanOltId
                INNER JOIN gpon as g ON o.OntGpon = g.IdOlt
                LEFT JOIN speed_profile_olts as s ON o.OntSpeedDown = s.IdProfile
                LEFT JOIN speed_profile_olts as u ON o.OntSpeedUp = u.IdProfile
                JOIN onu_type ot 
				ON (
				CONVERT(o.OntModelo USING utf8mb4) COLLATE utf8mb4_general_ci = 
				CONVERT(ot.OnuTypeName USING utf8mb4) COLLATE utf8mb4_general_ci
				)
                WHERE o.OntId = $id";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function GetVlanOnu($id)
    {
        $query = "SELECT v.IdVlan,v.ServicePortOnu,v.VportOnu,v.AttachedVlan,o.Vlan,o.VlanScope
                FROM vlans_onus as v
                INNER JOIN vlans_olt as o ON v.OltVlanId = o.VlanId
                where OnuId =$id ORDER BY ABS(ServicePortOnu)";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll();
        return $fetch;
    }
    //TRANSACTION
    public function DeleteVlanOnu($vlan)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('DELETE FROM vlans_onus
                                     WHERE IdVlan = ?');

        // Perform execute() inside a loop
        // Sample data coming from a fictitious data set, but the data can come from anywhere
        for ($i = 1; $i < count($vlan); $i++) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$vlan[$i]['IdVlan']]);
        }

        // Commit the data into the database
        $this->pdo->commit();
    }
    //TERMINA RESYNC ONU
    //MIGRACION
    public function getOnuGponM($zona)
    {
        $query = "SELECT g.IdOlt,o.OntId,g.IndexOid,g.IndexPort,g.IndexCard,o.OntPos,o.OnuSn 
                FROM gpon g
                INNER JOIN onu o ON g.IdOlt = o.OntGpon
                WHERE o.OntOlt = '$zona'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
}