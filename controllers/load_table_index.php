<?php
include 'query_db.php';

// Simula los datos consultados desde la base de datos.
$data = [];

function obtenerZona($cadena) {
    // Primero eliminamos el prefijo "zone_" y el sufijo que comienza con "_authd" o "_descr_"
    $cadena = preg_replace('/^zone_/', '', $cadena);  // Elimina el prefijo "zone_"
    $cadena = preg_replace('/(_authd|_descr_).*$/', '', $cadena);  // Elimina el sufijo

    // Reemplaza los guiones bajos por espacios
    $cadena = str_replace('_', ' ', $cadena);

    // Devuelve la cadena resultante
    return $cadena;
}

// Tu lógica para llenar $data con datos reales.
foreach ($sql as $row) {
    $data[] = [
        'nombre' => str_replace("_", " ", $row['OntNombre']),
        'status' => $row['OntStatus'],
        'potencia' => number_format($row['OntPotencia'] / 1000, 2),
        'modelo' => $row['OntModelo'],
        'zona' => obtenerZona($row['OntZona']),
        'olt' => $row['OntOlt'],
        'gpon' => $row['IndexCard'] . '/' .$row['IndexPort'],
        'index' => $row['IndexOid'] . $row['OntPos'],
        'serie' => $row['OnuSn'],
        'ver' => 'ont.php?' . 'id=' . $row['OntId']
    ];
}

// Retorna los datos como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>