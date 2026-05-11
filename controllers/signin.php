<?php

function usuarioExiste($email, $pdo) {

    $email = filter_var($email, FILTER_SANITIZE_EMAIL);

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE UsuarioCorreo = :email");

    $stmt->execute(array(':email' => $email));

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    return ($user !== false);
}


function registrarUsuario($name, $telefono, $email, $password, $privileges, $pdo) {

    $name = filter_var($name, FILTER_SANITIZE_STRING);
    $telefono = filter_var($telefono, FILTER_SANITIZE_STRING);
    $email = filter_var($email, FILTER_SANITIZE_STRING);
    $password = filter_var($password, FILTER_SANITIZE_STRING);
    $privileges = filter_var($privileges, FILTER_SANITIZE_STRING);


    if (usuarioExiste($email, $pdo)) {
        return false;
    }


    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    try {

        $stmt = $pdo->prepare("INSERT INTO usuarios (UsuarioNombre, UsuarioTelefono, UsuarioCorreo, UsuarioPass, UsuarioPrivilegios) VALUES (:name, :phone, :email, :password, :privileges)");

        $stmt->execute(array(':name' => $name, ':phone' => $telefono, ':email' => $email, ':password' => $hashed_password, ':privileges' => $privileges));

        return true;
        
    } catch (PDOException $e) {

        echo "Error al registrar el usuario: " . $e->getMessage();
    }
}


if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['signin'])) {
    $name = $_POST['name'] . " " . $_POST['apellidos'];
    $telefono = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $privileges = $_POST['privileges'];
    
    //registrarUsuario($email, $password, $pdo);

    if (registrarUsuario($name, $telefono, $email, $password, $privileges, $pdo)) {
        $message = 'registro';
    } else {
        $message = 'existe';
    }
}
?>
