<?php
require_once(__DIR__ . '../../../../db/conn.php');

class potenciaDb extends DbConn
{

    /**
     * VERSION 2 (2026-08-17): antes este INSERT no tenía ninguna
     * protección contra duplicados. Cuando formatOnu() clasifica por
     * error a una ONU ya existente como "nueva" -confirmado que ocurre
     * sobre todo en eventos de PwrFail, donde el serial reportado por
     * SNMP puede venir en un formato distinto al guardado en `onu`-
     * esta función insertaba una fila NUEVA de potencia cada vez, sin
     * tocar la fila real de esa ONU. La tabla `onu` está protegida por
     * el UNIQUE KEY uniq_onu_sn y por eso nunca se duplicó ahí, pero
     * `potencia` no tenía guardia equivalente: con miles de estos
     * eventos acumulados en un año, infló la tabla a 171,670 filas para
     * solo 18,803 ONUs reales.
     *
     * Ahora que `potencia` tiene UNIQUE KEY sobre `Onu` (ver
     * db/alter_potencia_unique_onu.sql), se usa
     * INSERT ... ON DUPLICATE KEY UPDATE: si la fila ya existe, se
     * actualiza con la lectura más reciente en vez de crear una fila
     * nueva. Esto no arregla la causa raíz de la mala clasificación,
     * pero convierte su efecto de "fuga de filas que crece para
     * siempre" a un no-op inofensivo.
     */
    public function insertPotencia($onu, $date)
    {
        if (empty($onu)) return false;

        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'INSERT INTO potencia (Potencia, Status, Distancia, Date, Onu)
                 VALUES (?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE
                    Potencia  = VALUES(Potencia),
                    Status    = VALUES(Status),
                    Distancia = VALUES(Distancia),
                    Date      = VALUES(Date)'
            );

            foreach ($onu as $p) {
                $stmt->execute([$p['rx'], $p['sta'], $p['dis'], $date, $p['id']]);
            }

            // NOTA: con ON DUPLICATE KEY UPDATE, rowCount() de MySQL no es
            // confiable como señal de éxito (puede devolver 0 si los
            // valores no cambiaron). Si no hubo excepción, se confirma.
            return $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[potenciaDb::insertPotencia] Fallo: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * VERSION 2 (2026-08-17): mismo fix que insertPotencia() de arriba,
     * aplicado a la inserción de una sola ONU (usada en el flujo de
     * autorización manual, api/onuProfile.php POST accion=Auth).
     */
    public function insertOnePotencia($p, $date)
    {
        try {
            $this->pdo->beginTransaction();

            $stmt = $this->pdo->prepare(
                'INSERT INTO potencia (Potencia, Status, Distancia, Date, Onu)
                 VALUES (?,?,?,?,?)
                 ON DUPLICATE KEY UPDATE
                    Potencia  = VALUES(Potencia),
                    Status    = VALUES(Status),
                    Distancia = VALUES(Distancia),
                    Date      = VALUES(Date)'
            );
            $stmt->execute([$p['rx'], $p['sta'], $p['dis'], $date, $p['id']]);

            return $this->pdo->commit();
        } catch (\Throwable $e) {
            if ($this->pdo->inTransaction()) {
                $this->pdo->rollBack();
            }
            error_log('[potenciaDb::insertOnePotencia] Fallo: ' . $e->getMessage());
            return false;
        }
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