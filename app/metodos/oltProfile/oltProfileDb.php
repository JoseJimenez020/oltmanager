<?php
require_once(__DIR__ . '../../../../db/conn.php');

class oltProfile extends DbConn
{

    public function GetOlt()
    {
        $query = "SELECT * FROM olts_list WHERE Activo = 1";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll();
        return $fetch;
    }
    public function getOltList()
    {
        $query = "SELECT OltIdApi, OltName FROM olts_list";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function GetOneOlt($id)
    {
        $query = "SELECT * FROM olts_list
                WHERE OltIdApi = $id";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch(PDO::FETCH_ASSOC);
        return $fetch;
    }

    public function GetSnmpProfile($host)
    {
        $query = "SELECT * FROM olts_list
                WHERE OltName = '$host'
                OR OltIdApi = '$host'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetch(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function UpdateOltProfile($data)
    {
        $this->pdo->beginTransaction();
        // Prepare statement
        $producto = $this->pdo->prepare('UPDATE olts_list
                                         SET  OltName = ?, 
                                         OltHardVer = ?,
                                         OltIpPrivate = ?,
                                         OltTelnetPort = ?,
                                         OltSnmpPort = ?,
                                         UserTelnet = ?,
                                         PassTelnet = ?,
                                         ReadComm = ?,
                                         WriteComm = ?,
                                         SoftVer = ?  
                                         WHERE OltIdApi = ?');

        $producto->execute([
            $data['olt_name'],
            $data['hardware_version'],
            $data['olt_ip'],
            $data['telnet_port'],
            $data['snmp_port'],
            $data['telnet_user'],
            $data['telnet_password'],
            $data['snmp_ro'],
            $data['snmp_rw'],
            $data['software_version'],
            $data['id']
        ]);
        $total = $producto->rowCount();
        if ($total > 0) {
            $this->pdo->commit();
            return $total;
        } else {
            $this->pdo->rollBack();
        }
    }

    public function InsertOlt($data)
    {
        try {
            $this->pdo->beginTransaction();

            // Calcular el próximo OltIdApi disponible
            $stmtMax = $this->pdo->query("SELECT COALESCE(MAX(OltIdApi), 0) + 1 FROM olts_list");
            $nextId  = (int) $stmtMax->fetchColumn();

            $stmt = $this->pdo->prepare(
                'INSERT INTO olts_list
                    (OltIdApi, OltName, OltHardVer, OltIpPrivate,
                     OltTelnetPort, OltSnmpPort,
                     UserTelnet, PassTelnet,
                     ReadComm, WriteComm, SoftVer)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)'
            );

            $stmt->execute([
                $nextId,
                $data['olt_name'],
                $data['hardware_version'],
                $data['olt_ip'],
                $data['telnet_port'],
                $data['snmp_port'],
                $data['telnet_user'],
                $data['telnet_password'],
                $data['snmp_ro'],
                $data['snmp_rw'],
                $data['software_version'],
            ]);

            if ($stmt->rowCount() > 0) {
                $this->pdo->commit();
                return $nextId;   // devuelve el OltIdApi asignado
            }

            $this->pdo->rollBack();
            return false;

        } catch (\PDOException $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            // Para depuración: error_log('InsertOlt error: ' . $e->getMessage());
            return false;
        }
    }
    public function getIndexOlt($olt = null)
    {
        $query = "SELECT o.OnuSn,o.OntId,o.OntPos,g.IndexOid,ig.IndexIntGpon,p.Status
                FROM onu o
                INNER JOIN gpon g
                ON o.OntGpon = g.IdOlt 
                INNER JOIN intgpon ig
                ON g.IdGpon = ig.IdIntGpon
                INNER JOIN potencia p 
                ON p.Onu = o.OntId";
        if (isset($olt))
            $query .= " AND o.OntOlt = $olt";
        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        return $fetch;
    }

    public function getVlanMgmtOlt($zona)
    {
        $query = "SELECT vo.VlanId,vo.Vlan FROM vlans_olt vo
                WHERE vo.VlanOltId = '$zona'
                AND vo.VlanScope = 'mgmt/voip'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_COLUMN | PDO::FETCH_GROUP);
        return $fetch;
    }
    public function getVlansMgmtByOlt($zona)
    {
        $query = "SELECT vo.VlanId,vo.Vlan FROM vlans_olt vo
                WHERE vo.VlanOltId = '$zona'
                AND vo.VlanScope = 'mgmt/voip'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll();
        return $fetch;
    }
}