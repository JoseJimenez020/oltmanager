<?php
session_start();
require '../db/conn.php';

$db = new DbConn();
$pdo = $db->getPdo();

function Sesion ($id, $pdo) {
  $stmt = $pdo->prepare('SELECT u.UsuarioId, u.UsuarioNombre, u.UsuarioCorreo, u.UsuarioPrivilegios, p.PrivilegioNombre FROM usuarios u INNER JOIN privilegios p ON u.UsuarioPrivilegios = p.PrivilegioId WHERE u.UsuarioId = :id');
  $stmt->bindParam(':id', $id);
  $stmt->execute();
  $results = $stmt->fetch(PDO::FETCH_ASSOC);

  $sesion = null;

  if (count($results) > 0) {
    $sesion = $results;
  }

  return $sesion;
}

function obtenerNombre($texto) {
  $palabras = explode(" ", trim($texto)); // Divide el texto por espacios
  return $palabras[0]; // Retorna la primera palabra
}

if (isset($_SESSION['user_id'])) {
    
$sesion = Sesion($_SESSION['user_id'], $pdo);

} else {
  $_SESSION['login_required'] = true;
  header("Location: ../index.php");
  exit();
}

if (isset($_SESSION['privilegio_required']) && $_SESSION['privilegio_required'] == true) {
  echo "<script>
    document.addEventListener('DOMContentLoaded', function () {
      Swal.fire({
        icon: 'info',
        title: 'Permiso denegado',
        text: 'No tienes permiso de acceder a esta interfaz.',
        confirmButtonColor: '#3085d6'
      });
    });
  </script>";
  unset($_SESSION['privilegio_required']);
}
?>