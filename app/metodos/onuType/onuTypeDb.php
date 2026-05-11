<?php
require_once(__DIR__ . '../../../../db/conn.php');

class onuType extends DbConn{
    
    public function GetOnuType(){
        $query="SELECT * FROM onu_type";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll();
        return $fetch;
    }

    public function InsertOnuType($name,$pon,$cap,$eth,$wifi,$pots){
        $query="INSERT INTO onu_type 
                (OnuTypeName, PonType, Capability, EthernetPorts,WifiPorts,VoipPorts) 
                VALUES ('$name', '$pon', '$cap', '$eth','$wifi','$pots')";
        
        $result= $this->pdo->exec($query);
        if ($result) {
            return true;
        }else {
            return false;
        }
    }

    public function DeleteOne($id){
        $query="DELETE FROM onu_type 
                WHERE (IdOnuType = '$id')";
        
        $result=$this->pdo->exec($query);
        
        return $result;
    }

    public function GetOne($id){
        $query="SELECT * FROM onu_type
                WHERE IdOnuType = $id";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetch(PDO::FETCH_ASSOC);
        return $fetch;
    }
}