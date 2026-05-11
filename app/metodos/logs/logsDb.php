<?php
require_once(__DIR__ . '../../../../db/conn.php');

class logsDb extends Dbconn{

    public function insertLogs($accion,$olt,$onu,$user,$ip,$date,$idOnu){
        $this->pdo->beginTransaction();
        // Prepare statement
        $producto = $this->pdo->prepare('INSERT
                                        INTO
                                        logs
                                        (Accion,Olt,Onu,User,Ip,Date,IdOnu)
                                        VALUES
                                        (?,?,?,?,?,?,?)');
        $producto->execute([$accion,$olt,$onu,$user,$ip,$date,$idOnu]);
        $total=$producto->rowCount();
        if($total > 0){
            $this->pdo->commit();
        }
        else{
            $this->pdo->rollBack();
        }
    }
    public function getLogsGeneral(){
        $query="SELECT l.Accion,ol.OltName,l.Onu,u.UsuarioCorreo,l.Ip,l.Date 
                FROM logs l
                INNER JOIN olts_list ol
                ON ol.OltIdApi = l.Olt
                INNER JOIN usuarios u
                ON u.UsuarioId = l.User";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll();
        return $fetch;
    }
    public function getLogsOnu($id){
        $query="SELECT l.Accion,ol.OltName,l.Onu,u.UsuarioCorreo,l.Ip,l.Date 
                FROM logs l
                INNER JOIN olts_list ol
                ON ol.OltIdApi = l.Olt
                INNER JOIN usuarios u
                ON u.UsuarioId = l.User
                WHERE l.IdOnu = '$id'";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll();
        return $fetch;
    }
}