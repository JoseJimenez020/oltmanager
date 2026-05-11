<?php
require_once(__DIR__ . '/logsDb.php');

class logsController
{
    private $db;

    public function __construct()
    {
        $this->db = new logsDb();
    }

    public function insertLogs($onu, $olt, $accion, $http, $type = true, $idOnu = null)
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        @session_start();
        $user = $_SESSION['user_id'];
        date_default_timezone_set('America/Merida');
        $date = date("Y-m-d H:i:s");
        $sum = $this->tipoAccion($onu, $accion, $http, $type);
        if ($type) {
            $gpon = "gpon-onu_1/{$onu['IndexCard']}/{$onu['IndexPort']}:{$onu['OntPos']}";
        } else {
            $gpon = null;
        }
        $this->db->insertLogs(
            $sum,
            $olt,
            $gpon,
            $user,
            $ip,
            $date,
            $idOnu
        );
    }
    public function tipoAccion($onu, $accion, $http, $type = true)
    {
        if ($type) {
            $gpon = "gpon-onu_1/{$onu['IndexCard']}/{$onu['IndexPort']}:{$onu['OntPos']}";
            $sn = "{$onu['OnuSn']}";
        }
        switch ($http) {
            case 'POST':
                if ($type) {
                    return "Se realiza en $gpon $accion";
                } else {
                    return "Se realiza $accion";
                }

                break;
            case 'PUT':
                if ($type) {
                    return "Se actualiza en $gpon $accion";
                } else {
                    return "Se actualiza $accion";
                }
                break;
            case 'DELETE':
                if ($type) {
                    return "Se elimina en $sn en $gpon $accion";
                } else {
                    return "Se elimina $accion";
                }

                break;
            default:
                # code...
                break;
        }
    }
    public function getLogsGeneral()
    {
        $result = $this->db->getLogsGeneral();
        return $result;
    }
    public function getLogsOnu($id)
    {
        $result = $this->db->getLogsOnu($id);
        return $result;
    }
}