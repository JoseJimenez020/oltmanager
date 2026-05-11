<?php
include 'connection.php';

// Simula los datos consultados desde la base de datos.
$data = [];

// Tu lógica para llenar $data con datos reales.
for ($i = count($name) - 1; $i >= 0; $i--) {
    $data[] = [
        'status' => $status[$i],
        'nombre' => str_replace("_", " ", $name[$i]),
        'modelo' => $model[$i],
        'zona' => obtenerZona($desc[$i]),
        'ver' => 'views/ont.php?pass=' . $pass . '&index=' . $index[$i]
    ];
}

// Retorna los datos como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>
