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

    /**
     * VERSION 2: antes hacía 1 UPDATE por cada ONU (1 round-trip por
     * fila). Con miles de ONUs "existe" en cada ciclo y latencia real
     * hacia la DB remota, esto explicó un salto a 234s en refresh_db
     * (variaba 12-41s antes, mismo patrón ya corregido en
     * historial_potencia, outage_event_onus y band_with, que aquí se
     * había quedado pendiente).
     *
     * UPDATE no soporta sintaxis multi-fila como INSERT, así que se usa
     * la técnica estándar de CASE WHEN por lotes: una sola sentencia
     * actualiza N filas con valores distintos cada una, en vez de N
     * sentencias individuales.
     */
    public function updateStatusOnus($status)
    {
        if (empty($status)) return false;

        try {
            $this->pdo->beginTransaction();

            $chunkSize = 200;
            $totalActualizados = 0;

            foreach (array_chunk($status, $chunkSize) as $chunk) {
                $casePotencia = '';
                $caseStatus   = '';
                $caseDate     = '';
                $paramsPotencia = [];
                $paramsStatus   = [];
                $paramsDate     = [];
                $ids = [];

                foreach ($chunk as $sta) {
                    $casePotencia .= 'WHEN ? THEN ? ';
                    $caseStatus   .= 'WHEN ? THEN ? ';
                    $caseDate     .= 'WHEN ? THEN ? ';
                    $paramsPotencia[] = $sta['id'];
                    $paramsPotencia[] = $sta['rx'];
                    $paramsStatus[]   = $sta['id'];
                    $paramsStatus[]   = $sta['status'];
                    $paramsDate[]     = $sta['id'];
                    $paramsDate[]     = $sta['date'];
                    $ids[] = $sta['id'];
                }

                $idsPlaceholders = implode(',', array_fill(0, count($ids), '?'));
                $sql = "UPDATE potencia
                        SET Potencia = CASE Onu $casePotencia END,
                            Status   = CASE Onu $caseStatus END,
                            Date     = CASE Onu $caseDate END
                        WHERE Onu IN ($idsPlaceholders)";

                $params = array_merge($paramsPotencia, $paramsStatus, $paramsDate, $ids);
                $stmt = $this->pdo->prepare($sql);
                $stmt->execute($params);
                $totalActualizados += $stmt->rowCount();

                // Mismo patrón preventivo que ya aplicamos en bandWithDb:
                // reiniciar el timer tras cada lote, sin depender de lo
                // que haya quedado de otro punto del código.
                set_time_limit(300);
            }

            if ($totalActualizados > 0) {
                return $this->pdo->commit();
            } else {
                return $this->pdo->rollBack();
            }
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[potenciaDb::updateStatusOnus] Fallo en lote: ' . $e->getMessage());
            return false;
        }
    }
}
