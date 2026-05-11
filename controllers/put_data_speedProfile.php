<?php
require_once(__DIR__ . '../../app/vendor/Telnet.php');
require_once(__DIR__ . '../../app/class/Olt.php');
require_once(__DIR__ . '../../app/metodos/OnuDb.php');

// Asegúrate de que se están enviando los datos correctamente
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $eolt=new OltManager();
    $eonu= new OnuDb();
    $eonuget=new OnuGet();
    // Recibir los datos del formulario
    $down = $_POST['down'] ?? '';  // Usamos el operador null coalescing para evitar errores
    $up = $_POST['up'] ?? '';
    $zona = $_POST['pass'] ?? 'error';
    $index = $_POST['index'] ?? 'error';

    $olt= $eolt->hostOlt();
    $telUser= $eolt->telUser();
    $telPass= $eolt->telPass();
    $Gpon=$eonu->GetCard($index);
    $Pos=$eonuget->GetPosIndex($index);
    // Puedes hacer alguna validación o procesamiento aquí (como guardarlos en la base de datos)
    $tel=new OpenSock($olt[$zona]);
    $text=$tel->putSpeedProTel($down,$up,$Gpon['IndexCard'],$Gpon['IndexPort'],$Pos,$telUser[$zona],$telPass[$zona]);
    // Respuesta a enviar de vuelta
    //$response = [
    //    'status' => 'success',
    //    'message' => "Datos recibidos: Down - $down, Up - $up, Zona - $pass, Correo - $index"
    //];

    // Devolver la respuesta en formato JSON
    echo json_encode($text);
}