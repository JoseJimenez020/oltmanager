<?php
require_once(__DIR__ . '../../../../db/conn.php');

class ipsDb extends DbConn{

    public function getIpsByOlt($id){
        $query="SELECT ip.id_Ip, ip.ipAddress FROM ips ip
                WHERE ip.olt_id = '$id'";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    //MGMT TR069
    public function getIpOlt($ip){
        $query="SELECT ip.ipAddress,ip.mask,ip.defaultGateway,ip.firstDns,ip.secDns 
                FROM ips ip
                WHERE ip.id_Ip = '$ip'";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetch(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function getIpsTable($id){
        $query="SELECT * FROM ips ip
                WHERE ip.olt_id = '$id'
                ORDER BY ABS(ip.ipAddress) ASC";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
}