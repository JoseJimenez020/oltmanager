<?php
if ($sesion['UsuarioPrivilegios'] !== 1) {
    
      $_SESSION['privilegio_required'] = true;
      header("Location: ../views/index.php");
      exit();

    }
?>