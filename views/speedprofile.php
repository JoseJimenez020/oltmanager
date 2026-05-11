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
            <h1>Speed Profile</h1>
            <div class="content" id="speed_profiles">
                <div class="container_settings">
                    <div class="settings_header">
                        <a href="olt_detail.php?id=<?php echo $_GET['id']; ?>"><button><img src="../img/return.png">Regresar</button></a>
                        <button><img src="../img/plus.svg">Add speed profile</button>
                    </div>

                    <!-- Botones para cambiar entre tablas -->
                    <div class="buttons_speedprofiles">
                        <button id="btnDownload"><img src="../img/down.png" alt="">Download</button>
                        <button id="btnUpload"><img src="../img/up.png">Upload</button>
                    </div>

                    <!-- Tabla Download -->
                    <div id="downloadContainer">
                        <h2>Download</h2>
                        <table id="speed_profileTable_download" class="display">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Use prefix&suffix</th>
                                    <th>Speed</th>
                                    <th>Type</th>
                                    <th>Default</th>
                                    <th>ONUs</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="speedDownTable">
                            </tbody>
                        </table>
                    </div>

                    <!-- Tabla Upload -->
                    <div id="uploadContainer">
                        <h2>Upload</h2>
                        <table id="speed_profileTable_upload" class="display">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Use prefix&suffix</th>
                                    <th>Speed</th>
                                    <th>Type</th>
                                    <th>Default</th>
                                    <th>ONUs</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody id="speedUpTable">
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- Formulario Speedprofile -->
            <div id="formModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <p>Crear nuevo Speed Profile</p>
                    <div class="container_form_settings">
                        <div class="content_form">
                            <form id="speedFormAdd">
                                <div class="form-group">
                                    <label for="profileName">Profile Name</label>
                                    <input type="text" id="name" name="name">
                                </div>
                                <!-- Radio Download/Upload -->
                                <div class="container_radio">
                                    <label for="download">Download</label>
                                    <input type="radio" name="type" id="download" value="down">
                                    <label for="upload">Upload</label>
                                    <input type="radio" name="type" id="upload" value="up">
                                </div>
                                <div class="form-group">
                                    <label for="type">Type</label>
                                    <select name="tipo" id="tipo">
                                        <option value="internet">Internet</option>
                                        <option value="iptv">IPTV</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="speed">Speed (in kbps)</label>
                                    <input type="text" name="speed" id="speed">
                                </div>
                                <input type="hidden" id="accion"name="accion" value="add">
                                <input type="hidden" id="olt"name="olt" value="<?php echo $_GET['id']?>">
                                <!-- Botones -->
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


        <!-- Modal para la eliminación de speed profile DOWNLOAD -->
        <div id="modalDeletespeedprofileDown" class="modal" style="display: none;">
            <div class="modal-content flex flex-row">
                <p>¿Estás seguro que deseas eliminar el perfil de velocidad download - <span
                        id="profileNameDown">SpeedProfile</span>?</p>
                <div class="container_delete_option">
                    <button class="noDeleteBtn no-btn">No</button>
                    <button class="yesDeleteBtn yes-btn">Sí</button>
                </div>
            </div>
        </div>
        <!-- Modal para la eliminación de speed profile UPLOAD -->
        <div id="modalDeletespeedprofileUp" class="modal" style="display: none;">
            <div class="modal-content flex flex-row">
                <p>¿Estás seguro que deseas eliminar el perfil upload - <span id="profileNameUp">SpeedProfile</span>?
                </p>
                <div class="container_delete_option">
                    <button class="noDeleteBtn no-btn">No</button>
                    <button class="yesDeleteBtn yes-btn">Sí</button>
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
    <script src="../js/load_table_speed.js"></script>
    <script src="../js/olt_manager_front_styles.js"></script>
</body>

</html>