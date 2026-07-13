<?php
require '../controllers/sesion.php';
require '../controllers/signin.php';
include '../controllers/alertas.php';
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../style/style.css">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico" />
    <title>Registrar Usuarios</title>
</head>

<body>
    <div class="container">
        <aside>
            <div class="top">
                <div class="logo">
                    <img src="../img/logo olt-manager.png">
                </div>
                <div class="close" id="close-btn">
                </div>
            </div>

            <div class="sidebar">
                <a href="index.php">
                    <span class="material-symbols-outlined">
                        grid_view
                    </span>
                    <h3>Inicio</h3>
                </a>
                <a href="unconfigured.php">
                    <span class="material-symbols-outlined">
                        contactless_off
                    </span>
                    <h3>Desautorizados</h3>
                </a>
                <a href="autorizados.php">
                    <span class="material-symbols-outlined">
                        task_alt
                    </span>
                    <h3>Autorizados</h3>
                </a>
                <a href="offline.php">
                    <span class="material-symbols-outlined">
                        signal_disconnected
                    </span>
                    <h3>Fuera de linea</h3>
                </a>
                <a href="lowsignal.php">
                    <span class="material-symbols-outlined">
                        signal_wifi_off
                    </span>
                    <h3>Señal debil</h3>
                </a>
                <!-- <a href=""> 
                    <span class="material-symbols-outlined">
                        monitoring
                        </span>
                    <h3>Graficas</h3>
                </a>
                
                <a href=""> 
                    <span class="material-symbols-outlined">
                        diagnosis
                        </span>
                    <h3>Diagnosticos</h3>
                </a>
                <a href="registro.php">
                    <span class="material-symbols-outlined">
                        how_to_reg
                        </span>
                    <h3>Registrar Usuarios</h3>
                </a>
                -->
                <a href="settings.php" class="active">
                    <span class="material-symbols-outlined">
                        tune
                    </span>
                    <h3>Opciones</h3>
                </a>
                <br>
                <a href="../controllers/logout.php" id="cerrar-sesion">
                    <span class="material-symbols-outlined">
                        power_settings_new
                    </span>
                    <h3>Cerrar Sesión</h3>
                </a>
            </div>
        </aside>
        <!-- END OF ASIDE -->
        <main>
            <!-- FINAL DE INSIGHTS -->
            <div class="ONUS">
                <h2>Registrar Usuario</h2>
                <a href="general.php"><button id="registro-regresar"><img src="../img/return.png"
                            alt="">Regresar</button></a>
                <form method="POST" onsubmit="return validarRegistro()">
                    <table class="registro_usuario">
                        <thead>
                            <tr>
                                <th>Informacion Necesaria</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Nombre:</td>
                                <td><input type="text" name="name" required></td>
                                <td>Apellidos:</td>
                                <td><input type="text" name="apellidos" required></td>
                            </tr>
                            <tr>
                                <td>Numero:</td>
                                <td><input type="number" name="phone" required></td>
                                <td>Correo:</td>
                                <td><input type="email" id="email" name="email" required></td>
                            </tr>
                            <tr>
                                <td>Contraseña:</td>
                                <td><input type="password" id="password" name="password" required></td>
                                <td>Permisos</td>
                                <td><select name="privileges" id="permisos" required>
                                        <option value="">Selecciona una opción</option selected>
                                        <option value="1">Administrador</option>
                                        <option value="2">Solo Leer</option>
                                        <option value="3">Call Center</option>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td id="registro_submit"><input type="submit" name="signin" value="Registrar"></td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
        </main>
        <!-- FINAL DE LA TABLA -->
        <div class="right">
            <div class="top">
                <button id="menu-btn">
                    <span class="material-symbols-outlined">
                        menu
                    </span>
                </button>
                <div class="theme-toggler">
                    <span class="material-symbols-outlined active">
                        brightness_high
                    </span>
                    <span class="material-symbols-outlined">
                        dark_mode
                    </span>
                </div>
                <div class="profile">
                    <div class="info">
                        <?php echo isset($sesion) ? "<p>Bienvenido, <b>" . obtenerNombre($sesion['UsuarioNombre']) . "</b></p>" : "<p><b>Inicia sesión</b></p>" ?>
                        <small
                            class="text-muted"><?php echo isset($sesion) ? $sesion['PrivilegioNombre'] : "" ?></small>
                    </div>
                    <div class="profile-photo">
                        <!-- <img src="profile-1.jpg" alt=""> -->
                    </div>
                </div>
            </div>
            <!-- FINAL DE TOP -->
            <!-- 
             <div class="historial">
                <h2>Historial</h2>
                <div class="updates">
                    <div class="update">
                        <div class="profile-photo">
                            <img src="profile-2.jpg" alt="">
                        </div>
                        <div class="message">
                            <p><b>Eloisa</b> Download speed changed to 40M	API	07-Nov-2024 10:51 </p>
                            <small class="text-muted">Hace 2 minutos</small>
                        </div>
                    </div>
                    <div class="update">
                        <div class="profile-photo">
                            <img src="profile-2.jpg" alt="">
                        </div>
                        <div class="message">
                            <p><b>Eloisa</b> Download speed changed to 40M	API	07-Nov-2024 10:51 </p>
                            <small class="text-muted">Hace 2 minutos</small>
                        </div>
                    </div>
                    <div class="update">
                        <div class="profile-photo">
                            <img src="profile-2.jpg" alt="">
                        </div>
                        <div class="message">
                            <p><b>Eloisa</b> Download speed changed to 40M	API	07-Nov-2024 10:51 </p>
                            <small class="text-muted">Hace 2 minutos</small>
                        </div>
                    </div>
                </div>
             </div>
              -->
            <!-- 
            <div class="temperaturas">
                <h2>Acciones</h2>
                <div class="item succesfuly">
                    <div class="icon">
                        <span class="material-symbols-outlined">restart_alt</span>
                    </div>
                    <div class="right">
                        <div class="info">
                            <h3>Reiniciar ONU</h3>
                        </div>
                    </div>
                </div>
                <div class="item elevada">
                    <div class="icon">
                        <span class="material-symbols-outlined">sync</span>
                    </div>
                    <div class="right">
                        <div class="info">
                            <h3>Resincronizar ONU</h3>
                        </div>
                    </div>
                </div>
                <div class="item online">
                    <div class="icon">
                        <span class="material-symbols-outlined">history</span>
                    </div>
                    <div class="right">
                        <div class="info">
                            <h3>Reestablecer por defecto</h3>
                        </div>
                    </div>
                </div>
                <div class="item offline">
                    <div class="icon">
                        <span class="material-symbols-outlined">delete</span>
                    </div>
                    <div class="right">
                        <div class="info">
                            <h3>Eliminar ONU</h3>
                        </div>
                    </div>
                </div>

            </div>
            -->
        </div>
    </div>
    <?php

    ?>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/theme_toggler.js"></script>
    <script src="../js/inputs-form.js"></script>

</html>