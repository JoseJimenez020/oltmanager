<?php
require_once(__DIR__ . '../../../../db/conn.php');

class speedProfile extends DbConn{
    
    public function GetSpeedProfile(){
        $query="SELECT * FROM speed_profile_olts";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll();
        return $fetch;
    }
    public function GetSpeedTypeProfile($type,$zone){
        $query="SELECT * FROM speed_profile_olts
                WHERE Tipo = '$type'
                AND Zona = '$zone'";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll();
        return $fetch;
    }
    public function getSpeedFormByIdOnu($id,$type){
        $query="SELECT sp.IdProfile, sp.ProfileName FROM speed_profile_olts sp
                INNER JOIN onu o ON o.OntId = $id
                WHERE sp.Zona = o.OntOlt
                AND Tipo = '$type'";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll(PDO::FETCH_ASSOC);
        return $fetch;
    }
    public function InsertSpeedProfile($name,$zona,$tipo,$speed){
        $query="INSERT INTO speed_profile_olts 
                (ProfileName, Zona, Tipo, Speed) 
                VALUES ('$name', '$zona', '$tipo', '$speed')";
        
        $result= $this->pdo->exec($query);
        if ($result) {
            return true;
        }else {
            return false;
        }
    }

    public function GetOneSpeed($id){
        $query="SELECT * FROM speed_profile_olts
                WHERE IdProfile = $id";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetch(PDO::FETCH_ASSOC);
        return $fetch;
    }

    public function DeleteOne($id){
        $query="DELETE FROM speed_profile_olts 
                WHERE IdProfile = '$id'";
        
        $result=$this->pdo->exec($query);
        
        return $result;
    }
    public function getSpeed($t,$zona){
        $query="SELECT IndexProfile,IdProfile
                FROM speed_profile_olts
                WHERE Tipo = '$t'
                AND Zona = '$zona'";
        
        $result=$this->pdo->prepare($query);
        $result->execute();
        $fetch=$result->fetchAll(PDO::FETCH_COLUMN|PDO::FETCH_GROUP);
        return $fetch;
    }
    public function insertSpeedOlts($p,$olt){
        // Start transaction
        $this->pdo->beginTransaction();

        // Prepare statement
        $stmt = $this->pdo->prepare('INSERT 
                                INTO speed_profile_olts (IndexProfile,ProfileName,Zona,Tipo) 
                                VALUES (?,?,?,?)');

        
        for ($i=0; $i < count($p['index']) ; $i++) {
            // All seven parameters are passed into the execute() in a form of an array
            $stmt->execute([$p['index'][$i],$p['value'][$i],$olt,$p['tipo'][$i]]);
        }

        // Commit the data into the database
        $this->pdo->commit();
    }
}