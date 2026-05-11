<?php
require_once(__DIR__ . '../../../../db/conn.php');

class vlanProfileDb extends DbConn
{

    public function getOltVlan($o)
    {
        $query = "SELECT v.VlanId,v.VlanOltId,v.Vlan,v.VlanDescription,v.VlanScope
                FROM vlans_olt as v
                LEFT JOIN olts_list as o ON v.VlanOltId = o.OltIdApi
                WHERE  o.OltName = '$o'
                OR v.VlanOltId = '$o'";


        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function insertVlanOnu($o)
    {
        try {
            $this->pdo->beginTransaction();

            // Prepare statement
            $stmt = $this->pdo->prepare('INSERT INTO vlans_onus
                                     (OnuId,ServicePortOnu,VportOnu,AttachedVlan,OltVlanId) 
                                     VALUES (?,?,?,?,?)');
            $totalUpdates = 0;
            //for ($i=0; $i < count($onus) ; $i++) {
            foreach ($o as $onus) {
                $stmt->execute([$onus['OnuId'], $onus['ServicePortOnu'], $onus['VportOnu'], $onus['AttachedVlan'], $onus['OltVlanId']]);
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
    public function GetVlanForm($o)
    {
        $query = "SELECT v.VlanId,v.VlanOltId,v.Vlan,v.VlanDescription,v.VlanScope
                FROM vlans_olt v
                WHERE  v.VlanOltId = '$o'
                AND v.VlanScope = 'internet'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function getVlansFormById($id)
    {
        $query = "SELECT v.VlanId,v.VlanOltId,v.Vlan,v.VlanDescription,v.VlanScope
                FROM vlans_olt v
                INNER JOIN onu o ON o.OntId = '$id'
                WHERE  v.VlanOltId = o.OntOlt
                AND v.VlanScope = 'internet'";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function getAllVlansOlt($id): ?array
    {
        try {
            $query = "SELECT 
                v.VlanId,
                v.Vlan,
                v.VlanDescription,
                v.VlanScope,
                COUNT(vo.OltVlanId) AS Total
                FROM vlans_olt v
                LEFT JOIN vlans_onus vo ON vo.OltVlanId = v.VlanId
                WHERE v.VlanOltId = :id
                GROUP BY v.VlanId, v.Vlan, v.VlanDescription, v.VlanScope";

            $stmt = $this->pdo->prepare($query);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return null;
        }
    }
    public function insertVlanOlt($data)
    {
        try {
            // Iniciar transacción
            $this->pdo->beginTransaction();

            // Limpiar y asignar variables
            $vlan = trim($data['vlan']);
            $desc = trim($data['desc']);
            $tipo = trim($data['tipo']);
            $olt = trim($data['olt']);

            // Verificar si ya existe la VLAN con ese ID de OLT
            $checkQuery = "SELECT COUNT(*) FROM vlans_olt WHERE Vlan = :vlan AND VlanOltId = :olt";
            $stmtCheck = $this->pdo->prepare($checkQuery);
            $stmtCheck->bindParam(':vlan', $vlan, PDO::PARAM_INT);
            $stmtCheck->bindParam(':olt', $olt, PDO::PARAM_INT);
            $stmtCheck->execute();

            if ($stmtCheck->fetchColumn() > 0) {
                $this->pdo->rollBack();
                return [
                    'status' => false,
                    'error' => 'La VLAN ya existe para ese OLT.'
                ];
            }
            $insertQuery = "INSERT INTO vlans_olt (Vlan, VlanDescription, VlanScope, VlanOltId)
                        VALUES (:vlan, :desc, :tipo, :olt)";
            $stmtInsert = $this->pdo->prepare($insertQuery);
            $stmtInsert->bindParam(':vlan', $vlan, PDO::PARAM_INT);
            $stmtInsert->bindParam(':desc', $desc, PDO::PARAM_STR);
            $stmtInsert->bindParam(':tipo', $tipo, PDO::PARAM_STR);
            $stmtInsert->bindParam(':olt', $olt, PDO::PARAM_INT);
            $stmtInsert->execute();

            $this->pdo->commit();

            return [
                'status' => true,
                'entity' => 'VLAN insertada correctamente.'
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return [
                'status' => false,
                'entity' => 'Error al insertar la VLAN: ' . $e->getMessage()
            ];
        }
    }
    public function deleteVlanOlt($data)
    {
        try {
            $this->pdo->beginTransaction();
            if (empty($data['id']) || !is_numeric($data['id'])) {
                return ['status' => false, 'error' => 'Dato no valido'];
            }

            $id = (int) $data['id'];

            $checkQuery = "SELECT COUNT(*) FROM vlans_onus WHERE OltVlanId = :id";
            $stmtCheck = $this->pdo->prepare($checkQuery);
            $stmtCheck->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtCheck->execute();
            $total = $stmtCheck->fetchColumn();
            if ($total > 0) {
                $this->pdo->rollBack();
                return [
                    'status' => false,
                    'error' => "No se puede eliminar, Onus usando vlan total: {$total}"
                ];
            }

            // Eliminar VLAN de la tabla principal
            $deleteQuery = "DELETE FROM vlans_olt WHERE VlanId = :id";
            $stmtDelete = $this->pdo->prepare($deleteQuery);
            $stmtDelete->bindParam(':id', $id, PDO::PARAM_INT);
            $stmtDelete->execute();

            $this->pdo->commit();
            return [
                'status' => true,
                'entity' => 'VLAN eliminada correctamente.'
            ];
        } catch (Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            return [
                'status' => false,
                'error' => 'Error al eliminar la VLAN: ' . $e->getMessage()
            ];
        }
    }

}