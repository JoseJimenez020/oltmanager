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

        <!-- ODBS -->
        <main>
            <h1>ODBs</h1>
            <div class="content" id="odbs">
                <div class="container_settings">
                    <div class="settings_header">
                        <a href="settings.php"><button><img src="../img/return.png">Regresar</button></a>
                        <button id="openFormModal"><img src="../img/plus.svg">Add ODB(Splitter)</button>
                        <!-- Agregamos el ID -->
                    </div>
                    <div class="search-filters">
                        <label for="zoneSearch">Buscar Zona:</label>
                        <input type="text" id="zoneSearch" placeholder="Buscar por Zona">

                        <label for="oltSearch">Buscar OLT:</label>
                        <input type="text" id="oltSearch" placeholder="Buscar por OLT">
                    </div>
                    <table id="odbsTable" class="display">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Nr of ports</th>
                                <th>Zone</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>ODB 1</td>
                                <td>4</td>
                                <td>Zone A</td>
                                <td><button class="delete">Delete</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL -->
            <div id="formModal" class="modal">
                <div class="modal-content">
                    <div class="container_form_settings">
                        <div class="content_form">
                            <form action="">
                                <!-- ODB Name -->
                                <div class="form-group">
                                    <label for="odb_name">ODB(Splitter)</label>
                                    <input type="text" id="odb_name" name="odb_name">
                                </div>

                                <!-- Nr of Ports -->
                                <div class="form-group">
                                    <label for="nr_ports">Nr of ports</label>
                                    <input type="text" id="nr_ports" name="nr_ports">
                                </div>

                                <!-- Zone -->
                                <div class="form-group">
                                    <label for="zone">Zone</label>
                                    <select id="zone" name="zone">
                                        <option value="">Select a zone</option>
                                        <option value="zone1">Zone 1</option>
                                        <option value="zone2">Zone 2</option>
                                    </select>
                                </div>

                                <!-- Latitude -->
                                <div class="form-group">
                                    <label for="latitude">Latitude</label>
                                    <input type="text" id="latitude" name="latitude">
                                </div>

                                <!-- Longitude -->
                                <div class="form-group">
                                    <label for="longitude">Longitude</label>
                                    <input type="text" id="longitude" name="longitude">
                                </div>

                                <!-- Botones -->
                                <div class="section_buttons">
                                    <button type="button" id="closeFormModal" class="cancel-btn">
                                        <img src="../img/cancel.png" alt="Cancel"> Cancel
                                    </button>
                                    <button type="submit" id="saveFormModal" class="submit-btn">
                                        <img src="../img/save.png" alt="Submit"> Save
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

        </main>

        <!-- Modal para la confirmación de eliminación de ODB -->
        <div id="modalDeleteODB" class="modal">
            <div class="modal-content flex fle-row">
                <p>¿Estás seguro que deseas eliminar el ODB -<span id="modalDeleteODBName">ODB 1</span>?</p>
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
    <script src="../js/load_table_odbs.js"></script>
    <script src="../js/olt_manager_front_styles.js"></script>
</body>

</html>