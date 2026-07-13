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
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico" />
    <title>Detalles OLT</title>
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
            <h1>Olt detail</h1>
            <div class="container_view_olt">
                <div class="container_settings">
                    <div class="settings_header">
                        <a href="OLTs.php"><button><img src="../img/return.png">Regresar</button></a>
                        <button id="editOltBtn">Edit OLT settings</button>
                        <a href="speedprofile.php?id=<?php echo $_GET['id']; ?>"><button>Speed Profile</button></a>
                        <a href="onu_type.php?id=<?php echo $_GET['id']; ?>"><button>Onu Types</button></a>
                        <a href="uplink.php?id=<?php echo $_GET['id']; ?>"><button>Uplink</button></a>
                        <a href="ONU_MgmtIPs.php?id=<?php echo $_GET['id']; ?>"><button>ONU Mgmt IPs</button></a>
                        <a href="vlans.php?id=<?php echo $_GET['id']; ?>"><button>Vlans</button></a>
                    </div>
                    <table id=oneOltTable>
                        <thead>
                            <tr>
                                <th>OLT settings</th>
                                <th>Value</th>
                            </tr>
                        </thead>
                        <tbody id="oneOltTables">
                            <tr>
                                <td>Name</td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td>OLT IP</td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td>Reachable via VPN tunnel</td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td>Telnet TCP port</td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td>OLT telnet username</td>
                                <td><input type="password"></td>
                            </tr>
                            <tr>
                                <td>OLT telnet password</td>
                                <td><input type="password"></td>
                            </tr>
                            <tr>
                                <td>SNMP read-only community </td>
                                <td><input type="password"></td>
                            </tr>
                            <tr>
                                <td>SNMP read-write community</td>
                                <td><input type="password"></td>
                            </tr>
                            <tr>
                                <td>SNMP UDP port</td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td>IPTV module</td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td>OLT hardware version </td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td>OLT software version</td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td>Supported PON types</td>
                                <td>Info</td>
                            </tr>
                            <tr>
                                <td>TR069 Profile</td>
                                <td class="olts_tr069">
                                    <select>
                                        <option value="info1">Info 1</option>
                                    </select>
                                    <button>Set profiles</button>
                                    <a href="">Manage TR069 profiles</a>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- MODAL EDIT OLT settings-->
            <div id="formModalEditOLT" class="modal">
                <div class="modal-content">
                    <div class="container_form_settings">
                        <div class="content_form">
                            <form id="editOltProfile">
                                <div class="form-group">
                                    <label for="olt_name">Name</label>
                                    <input type="text" id="olt_name" name="olt_name">
                                </div>

                                <div class="form-group">
                                    <label for="olt_ip">OLT IP or FQDN</label>
                                    <input type="text" id="olt_ip" name="olt_ip">
                                </div>

                                <div class="form-group">
                                    <label for="telnet_port">Telnet TCP port</label>
                                    <input type="text" id="telnet_port" name="telnet_port">
                                </div>

                                <div class="form-group">
                                    <label for="telnet_user">OLT telnet username</label>
                                    <input type="text" id="telnet_user" name="telnet_user">
                                </div>

                                <div class="form-group">
                                    <label for="telnet_password">OLT telnet password</label>
                                    <input type="text" id="telnet_password" name="telnet_password">
                                </div>

                                <div class="form-group">
                                    <label for="snmp_ro">SNMP read-only community</label>
                                    <input type="text" id="snmp_ro" name="snmp_ro">
                                </div>

                                <div class="form-group">
                                    <label for="snmp_rw">SNMP read-write community</label>
                                    <input type="text" id="snmp_rw" name="snmp_rw">
                                </div>

                                <div class="form-group">
                                    <label for="snmp_port">SNMP UDP port</label>
                                    <input type="text" id="snmp_port" name="snmp_port">
                                </div>

                                <!--<div class="form-group checkbox-group">
                                    <label for="iptv_module">IPTV module</label>
                                    <input type="checkbox" id="iptv_module" name="iptv_module">
                                </div>-->

                                <div class="form-group">
                                    <label for="hardware_version">OLT hardware version</label>
                                    <select id="hardware_version" name="hardware_version">
                                        <option value="">Select version</option>
                                        <option value="ZTE-C300">ZTE-C300</option>
                                        <option value="ZTE-C320">ZTE-C320</option>
                                    </select>
                                </div>

                                <div class="form-group">
                                    <label for="software_version">OLT software version</label>
                                    <select id="software_version" name="software_version">
                                        <option value="">Select version</option>
                                        <option value="2.x">2.x</option>
                                    </select>
                                </div>

                                <!--<div class="container_radio">
                                    <label>Supported PON types</label>
                                    <label><input type="radio" name="pon_type" value="GPON"> GPON</label>
                                    <label><input type="radio" name="pon_type" value="EPON"> EPON</label>
                                    <label><input type="radio" name="pon_type" value="XG-PON"> XG-PON</label>
                                </div>-->

                                <div class="section_buttons">
                                    <button type="button" id="closeFormModal" class="cancel-btn">
                                        <img src="../img/cancel.png" alt="Cancel">Cancel
                                    </button>
                                    <button type="submit" id="saveFormModal" class="submit-btn">
                                        <img src="../img/save.png" alt="Submit">Save
                                    </button>
                                    <button type="button" id="other_button" class="submit-btn test-connection">
                                        <img src="../img/test.png" alt="Test">Test connection
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
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

        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="../js/theme_toggler.js"></script>
    <script src="../js/load_olt_detail.js"></script>
    <script src="../js/olt_manager_front_styles.js"></script>
</body>

</html>