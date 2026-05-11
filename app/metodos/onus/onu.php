<?php
require_once(__DIR__ . '../../../../db/conn.php');

class Onus extends DbConn{
    public function GetOnusFiltradas($filtros){
        $query = "SELECT * FROM onu o
                INNER JOIN gpon g ON g.IdOlt = o.OntGpon
                INNER JOIN potencia p ON p.Onu = o.OntId
                INNER JOIN olts_list ol ON ol.OltIdApi = o.OntOlt 
                INNER JOIN status s ON s.StatusId = p.Status  
                WHERE 1";
        $params = [];

        if (!empty($filtros['contrato'])) {
        $query .= " AND o.OntNombre LIKE CONCAT('%', :contrato)";
        $params[':contrato'] = $filtros['contrato'];
        }

        if (!empty($filtros['nombre'])) {
        $query .= " AND o.OntNombre LIKE CONCAT(:nombre, '%')";
        $params[':nombre'] = str_replace(" ", "_", $filtros['nombre']);
        }

        if (!empty($filtros['SN'])) {
        $query .= " AND o.OnuSn LIKE :SN";
        $params[':SN'] = $filtros['SN'];
        }

        if (!empty($filtros['olt'])) {
        $query .= " AND ol.OltName = :olt";
        $params[':olt'] = $filtros['olt'];
        }

        if (!empty($filtros['tarjeta'])) {
        $query .= " AND g.IndexCard = :tarjeta";
        $params[':tarjeta'] = $filtros['tarjeta'];
        }

        if (!empty($filtros['puerto'])) {
        $query .= " AND g.IndexPort = :puerto";
        $params[':puerto'] = $filtros['puerto'];
        }

        $query .= " ORDER BY o.OntId";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $fetch;
    }

    public function GetOnusFiltradasOk($filtros){
        $query = "SELECT * FROM onu o 
                INNER JOIN gpon g ON  g.IdOlt = o.OntGpon
                INNER JOIN potencia p ON  p.Onu = o.OntId
                INNER JOIN olts_list ol ON ol.OltIdApi = o.OntOlt 
                INNER JOIN status s ON s.StatusId = p.Status 
                WHERE 1";
        $params = [];

        if (!empty($filtros['contrato'])) {
        $query .= " AND o.OntNombre LIKE CONCAT('%', :contrato)";
        $params[':contrato'] = $filtros['contrato'];
        }

        if (!empty($filtros['nombre'])) {
        $query .= " AND o.OntNombre LIKE CONCAT(:nombre, '%')";
        $params[':nombre'] = str_replace(" ", "_", $filtros['nombre']);
        }

        if (!empty($filtros['SN'])) {
        $query .= " AND o.OnuSn LIKE :SN";
        $params[':SN'] = $filtros['SN'];
        }

        if (!empty($filtros['olt'])) {
        $query .= " AND ol.OltIdApi = :olt";
        $params[':olt'] = $filtros['olt'];
        }

        if (!empty($filtros['tarjeta'])) {
        $query .= " AND g.IndexCard = :tarjeta";
        $params[':tarjeta'] = $filtros['tarjeta'];
        }

        if (!empty($filtros['puerto'])) {
        $query .= " AND g.IndexPort = :puerto";
        $params[':puerto'] = $filtros['puerto'];
        }

        if (!empty($filtros['status'])) {
        $query .= " AND p.Status = :status";
        $params[':status'] = $filtros['status'];
        }

        $query .= " ORDER BY o.OntId";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $fetch;
    }

    public function GetOnusFiltradasOff($filtros){
        $query = "SELECT * FROM onu o 
                INNER JOIN gpon g ON g.IdOlt= o.OntGpon
                INNER JOIN potencia p ON p.Onu = o.OntId
                INNER JOIN olts_list ol ON ol.OltIdApi = o.OntOlt
                INNER JOIN status s ON s.StatusId = p.Status
                WHERE p.Status IN (1, 4, 6)";
        $params = [];

        if (!empty($filtros['contrato'])) {
        $query .= " AND o.OntNombre LIKE CONCAT('%', :contrato)";
        $params[':contrato'] = $filtros['contrato'];
        }

        if (!empty($filtros['nombre'])) {
        $query .= " AND o.OntNombre LIKE CONCAT(:nombre, '%')";
        $params[':nombre'] = str_replace(" ", "_", $filtros['nombre']);
        }

        if (!empty($filtros['SN'])) {
        $query .= " AND o.OnuSn LIKE :SN";
        $params[':SN'] = $filtros['SN'];
        }

        if (!empty($filtros['olt'])) {
        $query .= " AND ol.OltIdApi = :olt";
        $params[':olt'] = $filtros['olt'];
        }

        if (!empty($filtros['tarjeta'])) {
        $query .= " AND g.IndexCard = :tarjeta";
        $params[':tarjeta'] = $filtros['tarjeta'];
        }

        if (!empty($filtros['puerto'])) {
        $query .= " AND g.IndexPort = :puerto";
        $params[':puerto'] = $filtros['puerto'];
        }

        $query .= " ORDER BY o.OntId";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $fetch;
    }

    public function GetOnusFiltradasLow($filtros){
        $query = "SELECT * FROM onu o 
                INNER JOIN gpon g ON g.IdOlt = o.OntGpon
                INNER JOIN potencia p ON p.Onu = o.OntId
                INNER JOIN status s ON s.StatusId = p.Status
                INNER JOIN olts_list ol ON ol.OltIdApi = o.OntOlt
                WHERE p.Potencia <= -30000 AND p.Potencia > -80000";
        $params = [];

        if (!empty($filtros['contrato'])) {
        $query .= " AND o.OntNombre LIKE CONCAT('%', :contrato)";
        $params[':contrato'] = $filtros['contrato'];
        }

        if (!empty($filtros['nombre'])) {
        $query .= " AND o.OntNombre LIKE CONCAT(:nombre, '%')";
        $params[':nombre'] = str_replace(" ", "_", $filtros['nombre']);
        }

        if (!empty($filtros['SN'])) {
        $query .= " AND o.OnuSn LIKE :SN";
        $params[':SN'] = $filtros['SN'];
        }

        if (!empty($filtros['olt'])) {
        $query .= " AND ol.OltIdApi = :olt";
        $params[':olt'] = $filtros['olt'];
        }

        if (!empty($filtros['tarjeta'])) {
        $query .= " AND g.IndexCard = :tarjeta";
        $params[':tarjeta'] = $filtros['tarjeta'];
        }

        if (!empty($filtros['puerto'])) {
        $query .= " AND g.IndexPort = :puerto";
        $params[':puerto'] = $filtros['puerto'];
        }

        $query .= " ORDER BY o.OntId";

        $stmt = $this->pdo->prepare($query);
        $stmt->execute($params);
        $fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $fetch;
    }

    public function GetTarjetas($Olt){
        $query = "SELECT g.IndexCard FROM onu o 
                INNER JOIN gpon g ON o.OntGpon = g.IdOlt
                INNER JOIN olts_list ol ON ol.OltIdApi = o.OntOlt 
                WHERE ol.OltIdApi = :olt 
                GROUP BY IndexCard";
        $result = $this->pdo->prepare($query);
        $result->bindParam(':olt', $Olt, PDO::PARAM_INT);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }

    public function GetPuertos($Olt, $Card){
        $query = "SELECT g.IndexPort FROM onu o 
                INNER JOIN gpon g ON o.OntGpon = g.IdOlt 
                INNER JOIN olts_list ol ON ol.OltIdApi = o.OntOlt
                WHERE ol.OltIdApi = :olt 
                AND g.IndexCard = :tarjeta 
                GROUP BY g.IndexPort";
        $result = $this->pdo->prepare($query);
        $result->bindParam(':olt', $Olt, PDO::PARAM_INT);
        $result->bindParam(':tarjeta', $Card, PDO::PARAM_INT);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }

    public function GetTiempo($id){
        $query="SELECT Date FROM potencia WHERE Onu = $id";      
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetch();
        return $fetch;
    }
}