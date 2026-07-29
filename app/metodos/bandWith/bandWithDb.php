<?php
// app\metodos\bandWith\bandWithDb.php
require_once(__DIR__ . '/../../../db/conn.php');

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

    /**
     * VERSION 2: antes hacía 1 INSERT por cada ONU (1 round-trip por fila).
     * Con miles de lecturas de banda ancha por ciclo y latencia real hacia
     * la DB remota (.33), esto era una fuente oculta de tiempo dentro de
     * refresh_db (mismo patrón ya corregido en historial_potencia y
     * outage_event_onus). Ahora se agrupa en lotes multi-fila.
     */
    public function insertBand(array $b) {
        if (empty($b)) return false;

        try {
            $this->pdo->beginTransaction();

            $chunkSize = 500;
            $totalInsertados = 0;

            foreach (array_chunk($b, $chunkSize) as $chunk) {
                $placeholders = implode(',', array_fill(0, count($chunk), '(?,?,?,?)'));
                $sql = "INSERT INTO band_with (IdOnu, RxBand, TxBand, Date) VALUES $placeholders";

                $params = [];
                foreach ($chunk as $band) {
                    $params[] = $band['IdOnu'];
                    $params[] = $band['Rx'];
                    $params[] = $band['Tx'];
                    $params[] = $band['Date'];
                }

                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $totalInsertados += $stmt->rowCount();
            }

            if ($totalInsertados > 0) {
                return $this->pdo->commit();
            } else {
                return $this->pdo->rollBack();
            }
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[bandWith::insertBand] Fallo en lote: ' . $e->getMessage());
            return false;
        }
    }
}
