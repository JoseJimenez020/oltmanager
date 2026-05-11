<?php
session_start();
require 'controllers/login.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Iniciar sesión</title>
  <link href="style/login.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

  <link rel="icon" type="image/x-icon" href="img/favicon.ico" />
  <script src="https://cdn.jsdelivr.net/npm/animejs@3.2.1/lib/anime.min.js"></script>
</head>

<body>
  <div class="page">
    <div class="container">
      <div class="left">
        <div class="login"><img src="img/logo-olt-manager.png"></div>
        <div class="eula">© 2025 Ont Networks SA de CV. Todos los derechos reservados.</div>
      </div>
      <div class="right">
        <svg viewBox="0 0 320 300">
          <defs>
            <linearGradient inkscape:collect="always" id="linearGradient" x1="13" y1="193.49992" x2="307" y2="193.49992"
              gradientUnits="userSpaceOnUse">
              <stop style="stop-color:#ff00ff;" offset="0" id="stop876" />
              <stop style="stop-color:#ff0000;" offset="1" id="stop878" />
            </linearGradient>
          </defs>
          <path
            d="m 40,120.00016 239.99984,-3.2e-4 c 0,0 24.99263,0.79932 25.00016,35.00016 0.008,34.20084 -25.00016,35 -25.00016,35 h -239.99984 c 0,-0.0205 -25,4.01348 -25,38.5 0,34.48652 25,38.5 25,38.5 h 215 c 0,0 20,-0.99604 20,-25 0,-24.00396 -20,-25 -20,-25 h -190 c 0,0 -20,1.71033 -20,25 0,24.00396 20,25 20,25 h 168.57143" />
        </svg>
        <div class="form">
          <form method="POST" onsubmit="return validarLogin()">
            <label for="email">Correo</label>
            <input type="email" name="email" id="email">
            <label for="password">Contraseña</label>
            <input type="password" name="password" id="password">
            <div class="container_section_pass">
              <input type="checkbox" id="show_password">
              <label for="watch_password">Mostrar contraseña</label>
            </div>
            <input type="submit" id="submit" name="login" value="Acceder">
          </form>
        </div>
      </div>
    </div>
  </div>
  <script src="js/login.js"></script>
  <script src="js/login.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

</body>

</html>