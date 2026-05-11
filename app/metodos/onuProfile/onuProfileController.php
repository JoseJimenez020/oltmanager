<?php
require_once(__DIR__ . '/onuDb.php');

class onuProfileController{
    private $db;

    public function __construct(){
        $this->db = new onuProfile();
    }
    //ONU PROFILE TABLE 
    public function getOnuProfileTable($id){
        $r = $this->db->getOnuProfileTable($id);
        $zona = $this->reZona($r['OntZona']);
        $desc = $this->reDesc($r['OntZona']);
        $port = "{$r['IndexPort']} (gpon-olt_1/{$r['IndexCard']}/{$r['IndexPort']})";
        $inter="gpon-onu_1/{$r['IndexCard']}/{$r['IndexPort']}:{$r['OntPos']}";
        unset($r['OntZona']);
        unset($r['IndexPort']);
        $r['Zona']=$zona;
        $r['Desc']=$desc;
        $r['IndexPort']=$port;
        $r['OnuInterface']=$inter;
        return $r;
    }
    private function reZona($cadena) {
        // Primero eliminamos el prefijo "zone_" y el sufijo que comienza con "_authd" o "_descr_"
        $cadena = preg_replace('/^zone_/', '', $cadena);  // Elimina el prefijo "zone_"
        $cadena = preg_replace('/(_authd|_descr_).*$/', '', $cadena);  // Elimina el sufijo
    
        // Reemplaza los guiones bajos por espacios
        $cadena = str_replace('_', ' ', $cadena);
    
        // Devuelve la cadena resultante
        return $cadena;
    }
    private function reDesc($cadena) {
        // Extrae la parte entre "_descr_" y "_authd" (o el final si no hay "_authd")
        if (preg_match('/_descr_(.*?)_authd/', $cadena, $matches)) {
            $descripcion = $matches[1];
        } else {
            return '-';
            // Si no encuentra _authd, intenta solo desde _descr_ en adelante
            $descripcion = preg_replace('/.*_descr_/', '', $cadena);
        }
    
        // Reemplaza guiones bajos por espacios
        $descripcion = str_replace('_', ' ', $descripcion);
    
        return $descripcion;
    }
    //ONU PROFILE TABLE
    public function updateStatus($sta){
        $result=$this->db->updateStatusOnus($sta);
        return $result;
    }
    public function GetOnus(){
        $result=$this->db->GetOnus();
        return $result;
    }
    public function getOnuBySn($s){
        $result = $this->db->getOnuBySn($s);
        return $result;
    }
    public function GetOnu($id){
        $result=$this->db->GetOnu($id);
        return $result;
    }
    public function getOnuSnId($zona){
        $result=$this->db->getOnuSnId($zona);
        return $result;
    }
    public function DeleteOnu($id){
        $result=$this->db->DeleteOnu($id);
        return $result;
    }
    public function DeleteBandWith($id){
        $result=$this->db->DeleteBandWith($id);
        return $result;
    }
    public function DeleteVlan($id){
        $result=$this->db->DeleteVlan($id);
        return $result;
    }
    public function DeletePotencia($id){
        $result=$this->db->DeletePotencia($id);
        return $result;
    }
    //INICIO INSERT ONU
    public function insertOnus($p){
        $result = $this->db->insertOnus($p);
        return $result;
    }
    public function insertOnu($gpon,$name,$type,$sn,$desc,$olt,$pos,$up,$down){
        $d=[
            'gpon'=>$gpon,
            'pos'=>$pos,
            'name'=>$name,
            'model'=>$type,
            'desc'=>$desc,
            'sn'=>$sn,
            'tcont'=>$up,
            'gemport'=>$down
        ];
        $result = $this->db->insertOnu($d,$olt);
        return $result;
    }
    public function getCard(){
        $result = $this->db->getCard();
        return  $result;
    }
    public function getOnuGponInfoVlan(){
        $result = $this->db->getOnuGponInfoVlan();
        return  $result;
    }
    //TERMINA INSERT ONU
    //INICIA RESYNC
    public function GetResyncOnu($id){
        $result = $this->db->GetResyncOnu($id);
        return $result;
    }
    public function GetVlanOnu($id){
        $result = $this->db->GetVlanOnu($id);
        return $result;
    }
    public function DeleteVlansOnu($vlan){
        $c=count($vlan);
        if ($c > 1) {
            $result = $this->db->DeleteVlanOnu($vlan);
            return $result;
        }
    }
    //TERMINA RESYNC
    //MIGRACION
    public function getOnuGponM($zona){
        $result = $this->db->getOnuGponM($zona);
        return $result;
    }
}