<?php
require_once(__DIR__ . '../../../../db/conn.php');

class bandWith extends DbConn
{
    protected $pdo;

    public function __construct()
    {
        $pdo = new DbConn();
        $this->pdo = $pdo->getPdo();
    }
    public function getAllWhere($id)
    {
        $query = "SELECT * FROM band_with
          WHERE IdOnu = $id 
          ORDER BY Date DESC
          LIMIT 10";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll();
        // Invertir los datos para mostrarlos cronológicamente (de viejo a nuevo)


        return $fetch;

    }
    public function getAll()
    {
        $query = "SELECT * FROM band_with";

        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll();
        return $fetch;
    }
    public function insertBand($b)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare('INSERT 
                                INTO band_with (IdOnu, RxBand, TxBand, Date) 
                                VALUES (?,?,?,?)');

            foreach ($b as $band) {
                $stmt->execute([$band['IdOnu'], $band['Rx'], $band['Tx'], $band['Date']]);
            }
            $total = $stmt->rowCount();
            if ($total > 0) { 
                return $this->pdo->commit();

            } else {
                return $this->pdo->rollBack();
            }
        } catch (Exception $e) {
            return $this->pdo->rollBack();
        }
    }
}