<?php
require '../controllers/sesion.php';
require '../controllers/load_form_autorizar.php';
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
    <title>Autorizar</title>
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
                <a href="unconfigured.php" class="active">
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
                <a href="settings.php">
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
                <h2>Autorizar Onu</h2>
                <form id="formAuth">
                    <table>
                        <thead>
                            <tr>
                                <th>Informacion Necesaria</th>
                                <th></th>
                                <th></th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>OLT:</td>
                                <td><input type="text" name="zona" required value="<?php echo $zona['OltName'] ?>"></td>
                                <td>SN:</td>
                                <td><input type="text" name="serie" required value="<?php echo $serie; ?>"></td>
                            </tr>
                            <tr>
                                <td>Tarjeta:</td>
                                <td><input type="text" name="tarjeta" required value="<?php echo $tarjeta; ?>"></td>
                                <td>Puerto:</td>
                                <td><input type="text" name="puerto" required value="<?php echo $puerto; ?>"></td>
                            </tr>
                            <tr>
                                <td>Tipo:</td>
                                <td>
                                    <select name="tipo" required>
                                        <?php foreach ($type as $ty) {
                                            $selected = ($ty['OnuTypeName'] === $tipoGet) ? 'selected' : '';
                                            ?>
                                            <option value="<?php echo $ty['IdOnuType'] ?>" <?php echo $selected ?>>
                                                <?php echo $ty['OnuTypeName'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>


                                <td>Vlan</td>
                                <td><select name="vlan" type="text" required>
                                        <?php foreach ($vlanPerOlt as $vlan) { ?>
                                            <option value="<?php echo $vlan['VlanId'] ?>">
                                                <?php echo $vlan['Vlan'] . ' ' . $vlan['VlanDescription'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Velocidad descarga</td>
                                <td><select name="down" type="text" required>
                                        <?php foreach ($download as $down) { ?>
                                            <option value="<?php echo $down['IdProfile'] ?>">
                                                <?php echo $down['ProfileName'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                                <td>Velocidad subida</td>
                                <td><select name="up" type="text" required>
                                        <?php foreach ($upload as $up) { ?>
                                            <option value="<?php echo $up['IdProfile'] ?>"><?php echo $up['ProfileName'] ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </td>
                            </tr>
                            <tr>
                                <td>Nombre:</td>
                                <td><input type="text" name="nombre" required></td>
                                <td>Comentario:</td>
                                <td><input type="text" name="comentario" required></td>
                            </tr>
                            <tr>
                                <td><label for="mode">Onu Mode</label></td>
                                <td>
                                    <select name="mode" id="mode">
                                        <option value="routing">Routing</option>
                                        <option value="bridging">Bridging</option>
                                    </select>
                                </td>
                                <!--<td><input type="hidden" name="inde" value="<?php echo $_GET['index']; ?>"></td>-->
                                <td><input type="hidden" name="accion" id="accion" value="Auth"></td>
                                <td><input type="hidden" name="olt" value="<?php echo $_GET['olt']; ?>"></td>
                            </tr>
                            <tr>
                                <td id="autorizar_submit"><input id="formAuth" type="submit" name="autorizar"
                                        value="Registrar"></td>
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
            FINAL DE HISTORIAL -->

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
        <script src="../js/autorizar.js"></script>

</html>