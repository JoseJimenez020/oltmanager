<?php
session_start();
require_once __DIR__ . '/../db/conn.php';
header('Content-Type: application/json; charset=utf-8');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Sesión requerida']);
    exit;
}

function limpiarZona($c) {
    $c = preg_replace('/^zone_/', '', $c);
    $c = preg_replace('/(_authd|_descr_).*$/', '', $c);
    return str_replace('_', ' ', $c);
}

try {
    $pdo = (new DbConn())->getPdo();
    $rows = $pdo->query(
        "SELECT OntZona, COUNT(*) AS total FROM onu
         WHERE OntZona IS NOT NULL AND OntZona <> ''
         GROUP BY OntZona"
    )->fetchAll(PDO::FETCH_ASSOC);

    // Varias cadenas crudas pueden dar la misma zona limpia: se agrupan
    $zonas = [];
    foreach ($rows as $r) {
        $n = limpiarZona($r['OntZona']);
        $zonas[$n] = ($zonas[$n] ?? 0) + (int) $r['total'];
    }
    ksort($zonas);

    $out = [];
    foreach ($zonas as $n => $t) {
        $out[] = ['nombre' => $n, 'total' => $t];
    }
    echo json_encode(['status' => true, 'message' => $out]);
} catch (Throwable $e) {
    error_log('[api/zonas] ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => false, 'message' => 'Error de base de datos']);
}