<?php
require_once(__DIR__ . '../../../../db/conn.php');

class Gpon extends DbConn{
    public function GetPonOutage(){
        $query = "SELECT 
                    ol.OltName,
                    o.OntGpon,
                    g.IndexCard,
                    g.IndexPort,
                    COUNT(*) AS total,
                    COUNT(CASE WHEN p.Status = 1 THEN 1 END) AS total_los,
                    COUNT(CASE WHEN p.Status = 4 THEN 1 END) AS total_pfail,
                    COUNT(CASE WHEN p.Status = 6 THEN 1 END) AS total_offline,
                    COUNT(CASE WHEN p.Status IN (1, 4, 6) THEN 1 END) AS total_caidos,
                    (
                        SELECT MAX(subp.Date)
                        FROM onu sub
                        INNER JOIN potencia subp ON sub.OntId = subp.Onu
                        WHERE subp.Status IN (1, 4, 6)
                          AND sub.OntOlt = o.OntOlt
                          AND sub.OntGpon = o.OntGpon
                    ) AS ultima_caida
                FROM onu o
                INNER JOIN gpon g ON o.OntGpon = g.IdOlt
                INNER JOIN olts_list ol ON o.OntOlt = ol.OltIdApi
                INNER JOIN potencia p ON o.OntId = p.Onu
                GROUP BY o.OntOlt, o.OntGpon, g.IndexCard, g.IndexPort
                HAVING total_caidos = total
                ORDER BY o.OntOlt, g.IndexCard, g.IndexPort;";
        $result = $this->pdo->prepare($query);
        $result->execute();
        $fetch = $result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function getGpon(){
        $query="SELECT IndexOid,IdOlt FROM gpon";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll(PDO::FETCH_UNIQUE | PDO::FETCH_ASSOC);
        return $fetch;
    }
}