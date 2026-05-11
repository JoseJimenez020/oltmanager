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
    <title>ONU Mgmt IPs</title>
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

        <!-- Uplink -->
        <main>
            <h1>ONU Mgmt IPs</h1>
            <div class="content" id="">
                <div class="container_settings">
                    <div class="settings_header">
                        <a href="olt_detail.php?id=<?php echo $_GET['id']; ?>"><button><img
                                    src="../img/return.png">Regresar</button></a>
                        <button>Add more management IPs</button>
                    </div>
                    <table id="ipsTable">
                        <thead>
                            <tr>
                                <th>Dirección Ip</th>
                                <th>Máscara</th>
                                <th>Gateway</th>
                                <th>DNS Primario</th>
                                <th>DNS Secundario</th>
                                <th>Accion</th>
                                <!-- <th>Action</th> -->
                            </tr>
                        </thead>
                        <tbody id="ipsTableBody">
                            <!--<tr>
                                
                            </tr>-->
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Modal para la configuración del uplink -->
        <div id="modaladdIPs" class="modal" style="display:none;">
            <div class="modal-content">
                <div class="container_form_settings">
                    <div class="content_form">
                        <form id="IpGenerator">
                            <!-- Campo oculto para el ID de la OLT -->
                            <input type="hidden" name="olt_id" id="olt_id" value="<?php echo $_GET['id']; ?>">

                            <div class="containerIpv4">
                                <label for="IPv4">Start IPv4 range</label>
                                <div class="containerIpInputs">
                                    <input type="number" value="" id="Octet1" name="Octet1" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                    <label for="">.</label>
                                    <input type="number" value="" id="Octet2" name="Octet2" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                    <label for="">.</label>
                                    <input type="number" value="" id="Octet3" name="Octet3" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                    <label for="">.</label>
                                    <input type="number" value="" id="Octet4" name="Octet4" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                </div>
                                <label for="IPv4">End IPv4 range</label>
                                <div class="containerIpInputs">
                                    <input type="number" value="" id="endOctet1" name="endOctet1" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                    <label for="">.</label>
                                    <input type="number" value="" id="endOctet2" name="endOctet2" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                    <label for="">.</label>
                                    <input type="number" value="" id="endOctet3" name="endOctet3" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                    <label for="">.</label>
                                    <input type="number" value="" id="endOctet4" name="endOctet4" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                </div>
                                <label for="IPv4">Subnet Mask</label>
                                <div class="containerIpInputs">
                                    <select name="mask" id="mask">
                                        <option value="255.255.255.252">/30 - 255.255.255.252</option>
                                        <option value="255.255.255.248">/29 - 255.255.255.248</option>
                                        <option value="255.255.255.240">/28 - 255.255.255.240</option>
                                        <option value="255.255.255.224">/27 - 255.255.255.224</option>
                                        <option value="255.255.255.192">/26 - 255.255.255.192</option>
                                        <option value="255.255.255.128">/25 - 255.255.255.128</option>
                                        <option value="255.255.255.0">/24 - 255.255.255.0</option>
                                        <option value="255.255.254.0">/23 - 255.255.254.0</option>
                                        <option value="255.255.252.0">/22 - 255.255.252.0</option>
                                        <option value="255.255.248.0">/21 - 255.255.248.0</option>
                                        <option value="255.255.240.0">/20 - 255.255.240.0</option>
                                        <option value="255.255.224.0">/19 - 255.255.224.0</option>
                                        <option value="255.255.192.0">/18 - 255.255.192.0</option>
                                        <option value="255.255.128.0">/17 - 255.255.128.0</option>
                                        <option value="255.255.0.0">/16 - 255.255.0.0</option>
                                    </select>
                                </div>
                                <label for="">Default gateway</label>
                                <div class="containerIpInputs">
                                    <input type="number" value="" id="gateOctet1" name="gateOctet1" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                    <label for="">.</label>
                                    <input type="number" value="" id="gateOctet2" name="gateOctet2" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                    <label for="">.</label>
                                    <input type="number" value="" id="gateOctet3" name="gateOctet3" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                    <label for="">.</label>
                                    <input type="number" value="" id="gateOctet4" name="gateOctet4" max="255" min="0"
                                        oninput="if(this.value.length > 3) this.value = this.value.slice(0, 3); if(this.value > 255) this.value=255;"
                                        required>
                                </div>
                                <label for="">DNS 1</label>
                                <div class="containerIpInputs">
                                    <input type="text" name="dns1" id="dns1" required>
                                </div>
                                <label for="">DNS 2</label>
                                <div class="containerIpInputs">
                                    <input type="text" name="dns2" id="dns2" required>
                                </div>
                                <input type="submit" value="Generar IPs">
                            </div>
                        </form>
                    </div>
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
    <script src="../js/olt_manager_front_styles.js"></script>
</body>

</html>