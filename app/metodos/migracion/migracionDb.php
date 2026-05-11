<?php
require_once(__DIR__ . '../../../../db/conn.php');
class migracionDb extends DbConn
{
    public function insertMigracions($migracion,$date)
    {

        try {
            $this->pdo->beginTransaction();

            $stmtCheck = $this->pdo->prepare("
            SELECT COUNT(*) FROM migracion
            WHERE idOnu = :idOnu
        ");

            $stmtInsert = $this->pdo->prepare("
            INSERT INTO migracion (indexNuevo,posNuevo, idOnu, date)
            VALUES (:indexNuevo,:posNuevo, :idOnu,:date)
        ");

            $insertados = 0;
            foreach ($migracion as $result) {
                $params = [
                    ':indexNuevo' => $result['indexNuevo'],
                    ':posNuevo' => $result['posNuevo'],
                    ':idOnu' => $result['idOnu'],
                    ':date' => $date
                ];

                $stmtCheck->execute([':idOnu' => $result['idOnu']]);
                $exists = $stmtCheck->fetchColumn();

                if ($exists == 0) {
                    $stmtInsert->execute($params);
                    $insertados += $stmtInsert->rowCount();
                }
            }

            if ($insertados > 0) {
                return $this->pdo->commit();
            } else {
                return $this->pdo->rollBack();
            }
        } catch (Exception $e) {
            $this->pdo->rollBack(); // Error: deshacer cambios
            return throw $e;
        }
    }
    public function updateMigracion($data)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('
            UPDATE onu
            SET OntGpon = ?, OntPos = ?
            WHERE OntId = ?
        ');

            $totalUpdates = 0;

            foreach ($data as $d) {

                $stmt->execute([
                    $d['indexNuevo'],
                    $d['posNuevo'],
                    $d['idOnu']
                ]);

                $totalUpdates += $stmt->rowCount();
            }

            if ($totalUpdates > 0) {
                return $this->pdo->commit();
            } else {
                return $this->pdo->rollBack();
            }
        } catch (Exception $e) {
            $this->pdo->rollBack(); 
            return $e; 
        }
    }
    public function deleteMigracion($data)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('
            DELETE FROM migracion
            WHERE Id = ?
        ');

            $totalDelete = 0;

            foreach ($data as $d) {

                $stmt->execute([
                    $d['Id']
                ]);

                $totalDelete += $stmt->rowCount();
            }

            if ($totalDelete > 0) {
                return $this->pdo->commit();
            } else {
                return $this->pdo->rollBack();
            }
        } catch (Exception $e) {
            $this->pdo->rollBack(); 
            return $e; 
        }
    }
    public function getMigracions(){
        $query="SELECT m.Id,m.date,m.posNuevo,g.IndexCard as cardNuevo,g.IndexPort as portNuevo,
                o.OntId,o.OntNombre,ol.OltName,
                gp.IndexCard as cardViejo,gp.IndexPort as portViejo,o.OntPos
                FROM migracion m
                INNER JOIN gpon g on g.IdOlt = m.indexNuevo
                INNER JOIN onu o on o.OntId = m.idOnu
                INNER JOIN gpon gp on gp.IdOlt = o.OntGpon
                INNER JOIN olts_list ol on ol.OltIdApi = o.OntOlt";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function getGponById($ids) {
    
    $placeholders = rtrim(str_repeat('?,', count($ids)), ',');

    $query = "SELECT idOnu, indexNuevo, posNuevo,idOnu,Id FROM migracion WHERE idOnu IN ($placeholders)";
    
    $stmt = $this->pdo->prepare($query);
    $stmt->execute($ids);
    
    $result = $stmt->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
    
    return $result;
    }

}
