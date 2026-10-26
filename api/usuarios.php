<?php
session_start();
require_once __DIR__ . '/../db/conn.php';

header('Content-Type: application/json; charset=utf-8');

// No usar controllers/sesion.php aquí: redirige con header() y rompe el JSON.
if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['status' => false, 'message' => 'Sesión requerida']);
    exit;
}

$pdo = (new DbConn())->getPdo();

// Solo administradores (privilegio 1)
$st = $pdo->prepare('SELECT UsuarioPrivilegios FROM usuarios WHERE UsuarioId = ?');
$st->execute([$_SESSION['user_id']]);
if ((int) $st->fetchColumn() !== 1) {
    http_response_code(403);
    echo json_encode(['status' => false, 'message' => 'Sin permisos']);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$body   = json_decode(file_get_contents('php://input'), true) ?? [];

try {
    if ($method === 'GET') {
        $rows = $pdo->query(
            'SELECT u.UsuarioId, u.UsuarioNombre, u.UsuarioTelefono, u.UsuarioCorreo,
                    u.UsuarioPrivilegios, p.PrivilegioNombre
             FROM usuarios u
             INNER JOIN privilegios p ON p.PrivilegioId = u.UsuarioPrivilegios
             ORDER BY u.UsuarioId'
        )->fetchAll(PDO::FETCH_ASSOC);   // nunca se envía UsuarioPass
        echo json_encode(['status' => true, 'message' => $rows]);

    } elseif ($method === 'PUT') {
        foreach (['id', 'nombre', 'telefono', 'correo', 'privilegio'] as $k) {
            if (empty($body[$k])) {
                http_response_code(400);
                echo json_encode(['status' => false, 'message' => "Falta $k"]);
                exit;
            }
        }
        if (!filter_var($body['correo'], FILTER_VALIDATE_EMAIL)) {
            echo json_encode(['status' => false, 'message' => 'Correo inválido']);
            exit;
        }
        // correo duplicado en otro usuario
        $dup = $pdo->prepare('SELECT COUNT(*) FROM usuarios WHERE UsuarioCorreo = ? AND UsuarioId <> ?');
        $dup->execute([$body['correo'], $body['id']]);
        if ($dup->fetchColumn() > 0) {
            echo json_encode(['status' => false, 'message' => 'Ese correo ya está en uso']);
            exit;
        }

        $sql    = 'UPDATE usuarios SET UsuarioNombre=?, UsuarioTelefono=?, UsuarioCorreo=?, UsuarioPrivilegios=?';
        $params = [$body['nombre'], $body['telefono'], $body['correo'], (int) $body['privilegio']];
        if (!empty($body['password'])) {                       // opcional
            $sql .= ', UsuarioPass=?';
            $params[] = password_hash($body['password'], PASSWORD_DEFAULT);
        }
        $sql .= ' WHERE UsuarioId=?';
        $params[] = (int) $body['id'];

        $pdo->prepare($sql)->execute($params);
        echo json_encode(['status' => true, 'message' => 'Usuario actualizado']);

    } elseif ($method === 'DELETE') {
        $id = (int) ($body['id'] ?? 0);
        if ($id === (int) $_SESSION['user_id']) {
            echo json_encode(['status' => false, 'message' => 'No puedes eliminarte a ti mismo']);
            exit;
        }
        $pdo->prepare('DELETE FROM usuarios WHERE UsuarioId = ?')->execute([$id]);
        echo json_encode(['status' => true, 'message' => 'Usuario eliminado']);
    }
} catch (PDOException $e) {
    error_log('[api/usuarios] ' . $e->getMessage());
    http_response_code(500);
    // FK: si el usuario tiene registros en logs, no se podrá borrar según tu esquema
    echo json_encode(['status' => false, 'message' => 'Error de base de datos']);
}