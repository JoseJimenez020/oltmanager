<?php
require_once(__DIR__ . '../../../../db/conn.php');

class potenciaDb extends DbConn
{

    public function insertPotencia($onu, $date)
    {
        try {

            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('INSERT 
                                INTO potencia (Potencia,Status,Distancia,Date,Onu) 
                                VALUES (?,?,?,?,?)');
            $totalUpdates = 0;
            foreach ($onu as $p) {
                $stmt->execute([$p['rx'], $p['sta'], $p['dis'], $date, $p['id']]);
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
    public function insertOnePotencia($p, $date)
    {
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO potencia (Potencia,Status,Distancia,Date,Onu) 
                                VALUES (?,?,?,?,?)');

        // All seven parameters are passed into the execute() in a form of an array
        $stmt->execute([$p['rx'], $p['sta'], $p['dis'], $date, $p['id']]);


        // Commit the data into the database
        $this->pdo->commit();
    }
    public function updateStatusOnus($status)
    {
        $this->pdo->beginTransaction();
        // Prepare statement
        $producto = $this->pdo->prepare('UPDATE
                                        potencia
                                        SET
                                        Potencia = ?,
                                        Status = ?,
                                        Date= ?
                                        WHERE Onu = ?');
        foreach ($status as $sta) {
            $producto->execute([$sta['rx'], $sta['status'], $sta['date'], $sta['id']]);
        }

        $total = $producto->rowCount();
        if ($total > 0) { 
            return $this->pdo->commit();
            
        } else {
            return $this->pdo->rollBack();
        }
    }
}