<?php
require '../controllers/sesion.php';
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
            <h1>Logs</h1>
            <!-- ONU types -->
            <div class="content" id="onu_types">
                <div class="container_settings">
                    <div class="settings_header">
                        <a href="index.php"><button><img src="../img/return.png">Regresar</button></a>
                    </div>
                    <!-- En tu archivo HTML, cambia la tabla a: -->
                    <table id="logsDataTable">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Acción</th>
                                <th>OLT</th>
                                <th>ONU</th>
                                <th>IP</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>

            <!-- ONY TYPE  ADD FORM -->
            <div id="onuFormModal" class="modal" style="display: none;">
                <div class="modal-content">
                    <p>Crear nuevo ONU Type</p>
                    <div class="container_form_settings">
                        <div class="content_form">
                            <form id="typeFormAdd">
                                <!-- PON Type -->
                                <div class="form-group">
                                    <label for="ponType">PON type</label>
                                    <div class="container_radio">
                                        <label for="pon">gpon</label>
                                        <input type="radio" id="pon" name="pon" value="gpon" required>
                                        <label for="pon">epon</label>
                                        <input type="radio" id="epon" name="pon" value="epon" required>
                                    </div>
                                </div>

                                <!-- ONU Type -->
                                <div class="form-group">
                                    <label for="onuType">ONU TYPE</label>
                                    <input type="text" name="name" id="name" required>
                                </div>

                                <!-- Ethernet Ports -->
                                <div class="form-group">
                                    <label for="ethernetPorts">Ethernet Ports</label>
                                    <select name="eth" id="eth">
                                        <option value="1">1 Port</option>
                                        <option value="2">2 Ports</option>
                                        <option value="3">3 Ports</option>
                                        <option value="4">4 Ports</option>
                                        <option value="5">5 Ports</option>
                                        <!-- <option value="8">8 Ports</option>
                                        <option value="16">16 Ports</option> -->
                                    </select>
                                </div>

                                <!-- WiFi SSIDs -->
                                <div class="form-group">
                                    <label for="wifiSSIDs">WiFi SSIDs</label>
                                    <select name="wifi" id="wifi">
                                        <option value="0">0 SSID</option>
                                        <option value="1">1 SSIDs</option>
                                        <option value="2">2 SSIDs</option>
                                        <option value="3">3 SSIDs</option>
                                        <option value="4">4 SSIDs</option>
                                        <option value="5">5 SSIDs</option>
                                        <option value="6">6 SSIDs</option>
                                    </select>
                                </div>

                                <!-- VoIP Ports -->
                                <div class="form-group">
                                    <label for="voipPorts">VoIP Ports</label>
                                    <select name="pots" id="pots">
                                        <option value="0">0 Port</option>
                                        <option value="1">1 Ports</option>
                                        <option value="2">2 Ports</option>
                                    </select>
                                </div>

                                <!-- Custom Profiles 
                                <div class="checkbox-group">
                                    <input type="checkbox" name="allowCustomProfiles" id="allowCustomProfiles">
                                    <label for="allowCustomProfiles">Allow custom profiles</label>
                                </div>-->

                                <!-- Default Custom Profile 
                                <div class="form-group">
                                    <label for="defaultCustomProfile">Default custom profile</label>
                                    <select name="defaultCustomProfile" id="defaultCustomProfile">
                                        <option value="profile1">Profile 1</option>
                                        <option value="profile2">Profile 2</option>
                                    </select>
                                </div>-->

                                <!-- Capability -->
                                <div class="container_radio">
                                    <label for="bridging">Bridging</label>
                                    <input type="radio" id="bridging" name="cap" value="Bridging" required>
                                    <label for="bridgingRouting">Bridging/Routing</label>
                                    <input type="radio" id="bridgingRouting" name="cap" value="Bridging/Routing"
                                        required>
                                </div>

                                <!-- ONU Type Image 
                                <div class="form-group">
                                    <label for="onuImage">ONU type image</label>
                                    <input type="file" name="onuImage" id="onuImage">
                                </div>-->
                                <input type="hidden" id="accion" name="accion" value="add">
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

        <!-- Modal para la confirmación de eliminación -->
        <div id="modalDeleteonutype" class="modal" style="display: none;">
            <div class="modal-content">
                <p>¿Estás seguro que deseas eliminar el ONU type - <span id="modalDeleteOnuName">ONU Type</span>?</p>
                <div class="container_delete_option">
                    <button id="noDeleteOnuBtn" class="no-btn">No</button>
                    <button id="yesDeleteOnuBtn" class="yes-btn">Sí</button>
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
                        <?php echo isset($sesion) ? "<p>Bienvenido, <b>" . obtenerNombre($sesion['UsuarioNombre']) . "</b></p>" : "<p><b>Inicia sesión</b></p>" ?>
                        <small
                            class="text-muted"><?php echo isset($sesion) ? $sesion['PrivilegioNombre'] : "" ?></small>
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
    <script src="../js/load_table_onutypes.js"></script>
    <!-- JS ARCHIVO fullLogs.js Tabla completa-->
    <script src="../js/fullLogs.js"></script>
</body>

</html>