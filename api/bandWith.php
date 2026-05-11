<?php
require_once(__DIR__ . '../../app/metodos/bandWith/bandWithController.php');

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $oper = isset($_GET['accion']) ? $_GET['accion'] : null;
    $id = isset($_GET['id']) ? $_GET['id'] : null;
    $band = new bandWithController();

    switch ($oper) {
        case 'all':
            $bw = $band->getWhere($id);
            $text = [
                'status' => true,
                'message' => $bw
            ];
            break;

        default:
            # code...
            break;
    }
    header('Content-Type: application/json');
    echo json_encode($text);
}