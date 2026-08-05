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
     * VERSION 4: la v3 reiniciaba el timer DESPUES de cada lote exitoso,
     * pero si el PRIMER lote por sí solo tarda más que el remanente
     * heredado de getBands() (120s), muere antes de llegar a ese reset.
     * Esto coincide con la misma lentitud errática del servidor de DB
     * (.33) que también afecta a purge_historial_potencia (514.7s en la
     * misma corrida) — probablemente contención/carga del lado del
     * servidor, no un problema de diseño del query. Mientras se investiga
     * esa causa de infraestructura, esta versión:
     *   1) reinicia el timer ANTES de empezar (no depende de lo que haya
     *      dejado getBands()),
     *   2) reduce el tamaño de lote de 500 a 200 filas, para acotar
     *      cuánto puede tardar UN SOLO execute() en el peor caso.
     */
    public function insertBand(array $b) {
        if (empty($b)) return false;

        // No confiar en el remanente que deje cualquier código anterior
        // (ej. bandWithController::getBands()) — arrancar con presupuesto
        // fresco antes de la primera escritura.
        set_time_limit(300);

        try {
            $this->pdo->beginTransaction();

            $chunkSize = 200; // antes 500: lotes más chicos acotan el
                               // peor caso si el servidor está lento
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

                // Reinicia tras cada lote también, por si el conjunto
                // completo de lotes (no uno solo) es lo que se alarga.
                set_time_limit(300);
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