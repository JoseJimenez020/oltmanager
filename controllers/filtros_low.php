<?php
include 'query_db.php';

function obtenerZona($cadena) {
    // Primero eliminamos el prefijo "zone_" y el sufijo que comienza con "_authd" o "_descr_"
    $cadena = preg_replace('/^zone_/', '', $cadena);  // Elimina el prefijo "zone_"
    $cadena = preg_replace('/(_authd|_descr_).*$/', '', $cadena);  // Elimina el sufijo

    // Reemplaza los guiones bajos por espacios
    $cadena = str_replace('_', ' ', $cadena);

    // Devuelve la cadena resultante
    return $cadena;
}

$inputJSON = file_get_contents("php://input");
$filtros = json_decode($inputJSON, true); // Convertir JSON a un array asociativo

// Asegurar que el array tenga todas las claves esperadas
$filtros = [
    'contrato' => $filtros['contrato'] ?? null,
    'nombre' =>  $filtros['nombre'] ?? null,
    'SN' => $filtros['SN'] ?? null, 
    'olt'      => $filtros['olt'] ?? null, 
    'tarjeta'  => $filtros['tarjeta'] ?? null, 
    'puerto'   => $filtros['puerto'] ?? null
];

$filtrar = $Onus->GetOnusFiltradasLow($filtros);
// Simula los datos consultados desde la base de datos.
$data = [];

// Tu lógica para llenar $data con datos reales.
foreach ($filtrar as $row) {
    $data[] = [
        'status' => $row['Status'],
        'nombre' => str_replace("_", " ", $row['OntNombre']),
        'potencia' => number_format($row['Potencia'] / 1000, 2),
        'serie' => $row['OnuSn'],
        'gpon' => "{$row['OltName']} gpon-onu_1/{$row['IndexCard']}/{$row['IndexPort']}:{$row['OntPos']}",
        'zona' => obtenerZona($row['OntZona']),
        'ver' => 'ont.php?id=' . $row['OntId'],
    ];
}

// Retorna los datos como JSON
header('Content-Type: application/json');
echo json_encode($data);
?>