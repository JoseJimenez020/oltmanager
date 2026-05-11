<?php
require '../controllers/sesion.php';
require '../controllers/privilegios.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Settings</title>
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
            <h1>Zones</h1>
            <!-- ZONES -->
            <div class="content" id="zones">
                <div class="container_settings">
                    <div class="settings_header">
                        <a href="settings.php"><button><img src="../img/return.png">Regresar</button></a>
                        <button id="openFormModal" class="open-form-modal">
                            <img src="../img/plus.svg" alt="Add"> Add Zone
                        </button>
                    </div>
                    <p>Utilice zonas para agrupar las ONU por ciudad, barrio o aldea. Las zonas son simples separaciones
                        de visualización que no influyen en la configuración de la ONU.</p>
                    <table id="zonesTable" class="display">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Info</td>
                                <td><button class="delete">Delete</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="formModal" class="modal">
                <div class="modal-content">
                    <div class="container_form_settings">
                        <div class="content_form">
                            <form action="">
                                <div class="form-group">
                                    <label for="zona">Zone</label>
                                    <input type="text" id="zona" name="zona">
                                </div>
                                <div class="section_buttons">
                                    <button type="button" id="closeFormModal" class="cancel-btn">
                                        <img src="../img/cancel.png" alt="Cancel">Cancel
                                    </button>
                                    <button type="submit" id="saveFormModal" class="submit-btn">
                                        <img src="../img/save.png" alt="Submit">Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Modal para la confirmación de eliminación -->
        <div id="modalDeleteZone" class="modal">
            <div class="modal-content flex fle-row">
                <p>¿Estás seguro que deseas eliminar la zona -<span id="modalDeleteZoneName">Zona 1</span>?</p>
                <div class="container_delete_option">
                    <button id="noDeleteZoneBtn" class="no-btn">No</button>
                    <button id="yesDeleteZoneBtn" class="yes-btn">Sí</button>
                </div>
            </div>
        </div>


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
                        <?php echo isset($sesion) ? "<p>Bienvenido, <b>" . obtenerNombre($sesion['UsuarioNombre']) . "</b></p>" : "<p><b>Inicia sesión</b></p>"?>
                        <small class="text-muted"><?php echo isset($sesion) ?  $sesion['PrivilegioNombre'] : "" ?></small>
                    </div>
                    <div class="profile-photo">
                        <!-- <img src="profile-1.jpg" alt=""> -->
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="../js/theme_toggler.js"></script>
    <script src="../js/load_table_zone.js"></script>
    <script src="../js/olt_manager_front_styles.js"></script>
</body>

</html>