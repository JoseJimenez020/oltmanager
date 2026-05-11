<?php
require_once(__DIR__ . '../../app/metodos/OnuGet.php');
require_once(__DIR__ . '../../app/metodos/OnuDb.php');
require_once(__DIR__ . '../../app/metodos/oltProfile/oltProfileController.php');
$eonu = new OnuDb();
$eonuget = new OnuGet();
$o = new oltProfileController();
$olts = $o->GetOlt();

$data = [];

$iter = 1;

// Tu lógica para llenar $data con datos reales.
foreach ($olts as $zona) {
    $res = $eonuget->Data($zona['OltName'], "OnuSnUncf");
    $key = array_keys($res);
    $res = array_values($res);
    $type = $eonuget->Data($zona['OltName'], "OnuType");
    $type = array_values($type);
    $ver = $eonuget->Data($zona['OltName'], "OnuVersion");
    $ver = array_values($ver);

    //foreach ($res as $key => $value) {
    for ($i = 0; $i < count($res); $i++) {

        //$pos = $eonuget->GetPosIndex($key[$i]);

        $co = strlen($res[$i]);
        $len = $eonuget->ParseSn($res[$i]);
        //$unt=strlen($len);
        if ($key[$i] != "NumeroError") {
            $card = $eonu->GetCard($key[$i]);

            $data[] = [
                'serie' => $len,
                'tipo' => str_replace('"', '', $type[$i]),
                'zona' => $zona['OltName'] . $card['IndexCard'] . '/' . $card['IndexPort'],
                'index' => $iter,
                'version' => str_replace('"', '', $ver[$i]),
                'ver' => '../views/autorizar.php?tarjeta=' . $card['IndexCard'] . '&puerto=' . $card['IndexPort'] . '&serie=' . $len . '&tipo=' . str_replace('"', '', $type[$i]) . '&olt=' . $zona['OltIdApi']
            ];
            $iter += 1;
        }
    }

}

// Retorna los datos como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>