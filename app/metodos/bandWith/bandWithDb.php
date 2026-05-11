<?php
require_once(__DIR__ . '/../../../../db/conn.php');

class bandWith extends DbConn {

    public function __construct() {
        parent::__construct();   // $this->pdo queda listo
    }

    public function getAllWhere($id) {
        $query = "SELECT * FROM band_with
                  WHERE IdOnu = :id
                  ORDER BY Date DESC
                  LIMIT 10";

        $result = $this->pdo->prepare($query);
        $result->bindParam(':id', $id, \PDO::PARAM_INT);
        $result->execute();
        return $result->fetchAll();
    }

    public function getAll() {
        $query  = "SELECT * FROM band_with";
        $result = $this->pdo->prepare($query);
        $result->execute();
        return $result->fetchAll();
    }

    public function insertBand(array $b) {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'INSERT INTO band_with (IdOnu, RxBand, TxBand, Date)
                 VALUES (?, ?, ?, ?)'
            );

            foreach ($b as $band) {
                $stmt->execute([
                    $band['IdOnu'],
                    $band['Rx'],
                    $band['Tx'],
                    $band['Date'],
                ]);
            }

            $total = $stmt->rowCount();
            if ($total > 0) {
                return $this->pdo->commit();
            } else {
                return $this->pdo->rollBack();
            }
        } catch (\Exception $e) {
            $this->pdo->rollBack();
            return false;
        }
    }
}
