<?php
session_start();
session_unset(); // Destruye las variables de sesión
session_destroy(); // Destruye la sesión
header("Location: ../"); // Redirige a la página principal
exit();
?>
