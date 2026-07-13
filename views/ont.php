<?php
require '../controllers/sesion.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../style/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico" />
    <!-- CDN CHARTS.JS GRAFICAS -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <title>Perfil de ONU</title>
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
                <a href="index.php" class="active">
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
            <div class="ONUS" id="onuProfileTable">
                <h2 id ="onuNombre"></h2>
                <table>
                    <thead>
                        <tr>
                            <th>Informacion del Cliente</th>
                            <th></th>
                            <th>Potencia y Estado</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>OLT:</td>
                            <td id="onuOltNombre"></td>
                            <td>Estado:</td>
                            <td class="status">
                            </td>
                        </tr>
                        <tr>
                            <td>Tarjeta:</td>
                            <td id="onuTarjeta"></td>
                            <td>ONU/OLT Rx Señal:</td>
                            <td class="distancia-potencia">
                            </td>
                        </tr>
                        <tr>
                            <td>Puerto:</td>
                            <td id="onuPuerto"></td>
                            <td>VLAN:</td>
                            <td>
                                <a id="botonAttachedVlan" style="cursor:pointer;" onclick="mostrarModal()">
                                    
                                </a>
                            </td>
                        </tr>
                        <tr>
                            <td>Onu:</td>
                            <td id="onuInterface"></td>
                        </tr>
                        <tr>
                            <td>Tipo:</td>
                            <td id="onuType"></td>
                        </tr>
                        <tr>
                            <td>Zone:</td>
                            <td id="onuZona"></td>
                            <td>ONU Mode:</td>
                            <td>
                                <a onclick="mostrarModal_onu_mode()" style="cursor:pointer;">Routing</a>
                            </td>
                        </tr>
                        <tr>
                            <td>SN:</td>
                            <td id="onuSn"></td>
                                <td>IP</td>
                                <td id="mgmtIpFrom"></td>
                        </tr>
                        <td>Wifi</td>
                        <td><input type="checkbox" name="wifi" class="wifi-toggle"></td>
                        <td>TR069</td>
                        <td class="tr069Class"><button id="tr069">Manage</button></td>
                        <tr>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div id="modal_vlan" class="modal-vlan">
                <div class="contenido_modal">
                    <span class="cerrar_modal" onclick="cerrarModal()">&times;</span>
                    <h2>Update VLANs</h2>
                    <form id="vlan_form" method="POST">
                        <div class="container-vlan" id="contenido">
                            <label for="VLANS">VLANs</label>
                            <div class="custom-select">
                                <div class="select-box">
                                    <span id="vlans" name="vlans">Seleccionar VLAN</span>
                                    <span>&#9660;</span>
                                </div>
                                <ul class="dropdown" id="vlan-dropdown">
                                </ul>
                                <input type="hidden" id="id" name="id" value="<?php echo $_GET['id']; ?>">
                                <input type="hidden" id="accion" name="accion" value="attachedVlan">
                            </div>
                            <button type="button" id="vlan_update">Update</button>
                        </div>
                    </form>
                </div>
            </div>
            <!-- Modal wifi -->
            <div id="modalToggleWifi" class="modal" style="display: none;">
                <div class="modal-content flex flex-col">
                    <p>¿Qué acción deseas realizar con el WiFi - <span id="modalWifiName">Nombre WiFi</span>?</p>
                    <div class="container_delete_option flex justify-between mt-4">
                        <button id="noToggleWifiBtn" class="no-btn">Cancelar</button>
                        <button id="disableWifiBtn" data-accion="wifi" data-wifi="disable"
                            data-id="<?php echo $_GET['id'] ?>" class="no-btn">Deshabilitar</button>
                        <button id="enableWifiBtn" data-accion="wifi" data-wifi="enable"
                            data-id="<?php echo $_GET['id'] ?>" class="yes-btn">Habilitar</button>
                    </div>
                </div>
            </div>
            <div id="modalToggleWifi" class="modal" style="display: none;">
                <div class="modal-content flex flex-col">
                    <p>¿Qué acción deseas realizar con el WiFi - <span id="modalWifiName">Nombre WiFi</span>?</p>
                    <div class="container_delete_option flex justify-between mt-4">
                        <button id="noToggleWifiBtn" class="no-btn">Cancelar</button>
                        <button id="disableWifiBtn" class="no-btn">Deshabilitar</button>
                        <button id="enableWifiBtn" class="yes-btn">Habilitar</button>
                    </div>
                </div>
            </div>
            <div id="modal_onu_mode" class="modal-onu_mode">
                <div class="contenido_modal">
                    <span class="cerrar_modal" onclick="cerrarModal_mode()">&times;</span>
                    <h2>Update ONU mode</h2>
                    <form id="onu_mode_form">
                        <div class="container-onu_mode" id="contenido">

                            <!-- Dropdown 1: WAN VLAN-ID 
                            <div class="custom-select vlan-select">
                             <h2>WAN VLAN-ID</h2> 
                            <div class="select-box vlan-box">
                                <span>ATTACHED VLANs</span>
                                <span>&#9660;</span>
                            </div>
                            <ul class="dropdown vlan-dropdown">
                                <li><input type="checkbox" name="vlan1" value="1" id="vlan1"><label for="vlan1">VLAN
                                        1</label></li>
                                <span>Other VLANs</span>
                                <li><input type="checkbox" name="vlan2" value="2" id="vlan2"><label for="vlan2">VLAN
                                        2</label></li>
                            </ul>
                        </div>
                        -->
                            <!-- ONU Mode -->
                            <ul class="onu_mode">
                                <h2>ONU Mode</h2>
                                <label for="routing">Routing</label>
                                <input value="routing" type="radio" name="onu_mode" id="routing">
                                <label for="bridging">Bridging</label>
                                <input value="bridging" type="radio" name="onu_mode" id="bridging">
                            </ul>

                            <!-- WAN Mode 
                            <ul class="wan_mode">
                                <h2>WAN Mode</h2>
                                <label for="Setup_via_onu_webpage">SetUp via ONU webpage</label>
                                <input type="radio" name="wan_mode" id="Setup_via_onu_webpage">
                                <h3>Settings for compatible ONUs:</h3>
                                <label for="dhcp">DHCP</label>
                                <input type="radio" name="wan_mode" id="dhcp">
                                <label for="static_ip">Static IP</label>
                                <input type="radio" name="wan_mode" id="static_ip">
                                <label for="PPPoE">PPPoE</label>
                                <input type="radio" name="wan_mode" id="PPPoE">
                            </ul>
                            -->

                            <!-- Dropdown 2: WAN Remote Access 
                            <div class="custom-select remote-select">
                                <h2>WAN Remote Access</h2>
                                <div class="select-box remote-box">
                                    <span>Disabe/Not set</span>
                                    <span>&#9660;</span>
                                </div>
                                <ul class="dropdown remote-dropdown">
                                    <li><input type="checkbox" name="remote_vlan1" value="1" id="remote_vlan1"><label
                                            for="remote_vlan1">VLAN 1</label></li>
                                    <li><input type="checkbox" name="remote_vlan2" value="2" id="remote_vlan2"><label
                                            for="remote_vlan2">VLAN 2</label></li>
                                </ul>
                            </div>
                            -->
                            <input type="hidden" id="id" name="id" value="<?php echo $_GET['id']; ?>">
                            <input type="hidden" id="accion" name="accion" value="onuMode">
                            <button type="submit" id="vlan_update">Update</button>

                        </div>
                    </form>
                </div>
            </div>
            <div class="menu-status">
                <button id="mostrarDatosBtn" data-accion="status" data-id="<?php echo $_GET['id'] ?>">Obtener
                    estado</button>
                <button id="running-config" data-accion="config" data-id="<?php echo $_GET['id'] ?>">Obtener
                    configuracion</button>
                <button id="mostrarIpMac" data-accion="ipMac" data-id="<?php echo $_GET['id'] ?>">Obtener Ip</button>
            </div>
            <div id="resultado"></div>

            <div id="logsContainer" class="containerTableLogs" style="display: none;">
                <h2>Logs ONU</h2>
                <table id="logsOntTable" style="width:100%">
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

            <div class="container_graphics">
                <div class="graphics_grid">
                    <div class="chart-title" id="date-title"></div>
                    <div class="scroll-container">
                        <canvas id="graphic-min" height="200"></canvas>
                    </div>
                </div>
                <div class="graphics_grid">
                    <div class="chart-title" id="date-title"></div>
                    <div class="scroll-container">
                        <canvas id="graphic-hour" height="200"></canvas>
                    </div>
                </div>
            </div>

            <div class="container-perfil-vel">
                <h2>Speed Profile</h2>
                <div class="rows">
                    <div class="col" id="title">Download</div>
                    <div class="col" id="title">Upload</div>
                    <div class="col" id="title">Action</div>
                    <div id ="onuSpeedDown"class="col"></div>
                    <div id="onuSpeedUp"class="col"></div>
                    <div class="col">
                        <button class="botonF" id="openPopup" style="color: black;"> <!--<img src="../img/gear.gif" alt="gear"> -->Configure</button>
                        <section id="loginPopup" class="hidden">
                            <div class="contenedor-login">
                                <span id="closePopup" class="close-btn">&times;</span>
                                <div class="formulario">
                                    <form id="speedProfile">
                                        <h2>Speed Profile</h2>
                                        <div class="input-contenedor">
                                            <i class="fa-solid fa-envelope"></i>
                                            <!--<input id="down" type="text" name="down" required>
                                            <label for="down">Down</label> -->
                                            <tr>
                                                <td><label for="#">Velocidad descarga</label></td>
                                                <td><select id="selectSpeedDown"name="down" type="text" required>
                                                    </select>
                                                </td>
                                            </tr>
                                        </div>

                                        <div class="input-contenedor">
                                            <i class="fa-solid fa-lock"></i>
                                            <!--<input id="up" type="text" name="up" required>
                                            <label for="up">Up</label>-->
                                            <tr>
                                                <td><label for="#">Velocidad subida</label></td>
                                                <td><select id="selectSpeedUp"name="up" type="text" required>
                                                    </select>
                                                </td>
                                            </tr>
                                            <input type="hidden" id="id" name="id" value="<?php echo $_GET['id']; ?>">
                                            <input type="hidden" id="accion" name="accion" value="speedProfile">
                                        </div>
                                        <button type="submit" name="login" id="enviar-popup">Enviar</button>
                                    </form>
                                </div>
                            </div>
                        </section>
                        <section id="gestionPopup" class="hidden">
                            <div class="contenedor-login">
                                <span id="closePopup" class="close-btn">&times;</span>
                                <div class="formulario">
                                    <form id="gestionOnu">
                                        <h2>Gestion de ONU</h2>
                                        <div class="input-contenedor">
                                            <i class="fa-solid fa-envelope"></i>
                                            <!--<input id="down" type="text" name="down" required>
                                <label for="down">Down</label> -->
                                            <tr>
                                                <td><label for="#">Vlan Adjunta</label></td>
                                                <td><select id ="vlansSelectForm"name="vlan" type="text" required>
                                                    </select>
                                                </td>
                                            </tr>
                                        </div>

                                        <div class="input-contenedor">
                                            <i class="fa-solid fa-lock"></i>
                                            <!--<input id="up" type="text" name="up" required>
                                <label for="up">Up</label>-->
                                            <tr>
                                                <td><label for="#">Configuracion ONU</label></td>
                                                <td><select name="config" type="text" required>
                                                        <option value="default">Configuracion por default</option>
                                                        <option value="dhcp">Configuracion DHCP</option>
                                                    </select>
                                                </td>
                                            </tr>
                                            <input type="hidden" id="id" name="id" value="<?php echo $_GET['id']; ?>">
                                            <input type="hidden" id="accion" name="accion" value="gestion">
                                        </div>
                                        <button id="enviar-popup" type="submit" name="login">Enviar</button>
                                    </form>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <div id="modalTR069" class="classTR069">
                <div class="modal-content">
                    <div class="container_form_settings">
                        <div class="content_form">
                            <h2>Update Management VoIP IP</h2>
                            <form id="tr069Form">
                                <div class="tr69Options">

                                    <div class="firstLine">
                                        <label for="tr069">TR069 profile</label>
                                        <select name="profile" id="">
                                            <option value="disable">Disable</option>
                                            <option value="smartolt">SmartOlt</option>
                                        </select>
                                    </div>


                                    <div class="secLine">
                                        <label for="Mgmt">Mgmt IP</label>
                                        <div class="radioContainer">
                                            <input value="disable" type="radio" name="mgmt" id="radioInactive" checked>
                                            <label for="radioInactive">Inactive</label>
                                        </div>
                                        <div class="radioContainer">
                                            <input value="static" type="radio" name="mgmt" id="radioStaticIp">
                                            <label for="radioStaticIp">Static IP</label>
                                        </div>
                                    </div>

                                    <div class="containerStaticIp" id="staticIp">
                                        <div class="radiofirstLine">
                                            <label for="access">Permiter acceso remoto a Mgmt IP para todos</label>
                                            <input value="allow" type="checkbox" name="access" id="access">
                                        </div>
                                        <div class="radiosecLine">
                                            <label for="mgmtVlan">Mgmt VLAN-ID</label>
                                            <select name="mgmtVlan" id="mgmtVlan">

                                            </select>
                                        </div>
                                        <div class="radiothirdLine">
                                            <label for="mgmtIp">Mgmt IP address</label>
                                            <select name="mgmtIp" id="mgmtIp">
                                                <option value="">Opcion</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="thirdLine">
                                        <label>VoIP service</label>
                                        <div class="radioContainer">
                                            <input value="disable" type="radio" name="voipService" id="voipDisabled"
                                                checked>
                                            <label for="voipDisabled">Disabled</label>
                                            <!-- Cambiado para que coincida con el id -->
                                        </div>
                                        <div class="radioContainer">
                                            <input value="enable" type="radio" name="voipService" id="voipEnabled">
                                            <!-- Mismo name que el otro radio -->
                                            <label for="voipEnabled">Enable (general switch)</label>
                                        </div>
                                    </div>

                                    <div id="containerVoIP" class="containerVoIP">
                                        <label>Attach VoIP to</label>
                                        <div class="radioContainer">
                                            <input value="mgmt" type="radio" name="voipOption" id="radioMgmt" checked>
                                            <label for="radioMgmt">Mgmt</label>
                                        </div>
                                        <div class="radioContainer">
                                            <input value="wan" type="radio" name="voipOption" id="radioWan">
                                            <label for="radioWan">WAN</label>
                                        </div>
                                    </div>
                                    <input type="hidden" id="id" name="id" value="<?php echo $_GET['id']; ?>">
                                    <input type="hidden" id="accion" name="accion" value="tr069">
                                    <input type="submit">
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
            <!-- FINAL DE TOP -->
            <!-- <div class="historial">
                <h2>Historial</h2>
                <div class="updates">
                    <div class="update">
                        <div class="profile-photo">
                            <img src="profile-2.jpg" alt="">
                        </div>
                        <div class="message">
                            <p><b>Eloisa</b> Download speed changed to 40M API 07-Nov-2024 10:51 </p>
                            <small class="text-muted">Hace 2 minutos</small>
                        </div>
                    </div>
                    <div class="update">
                        <div class="profile-photo">
                            <img src="profile-2.jpg" alt="">
                        </div>
                        <div class="message">
                            <p><b>Eloisa</b> Download speed changed to 40M API 07-Nov-2024 10:51 </p>
                            <small class="text-muted">Hace 2 minutos</small>
                        </div>
                    </div>
                    <div class="update">
                        <div class="profile-photo">
                            <img src="profile-2.jpg" alt="">
                        </div>
                        <div class="message">
                            <p><b>Eloisa</b> Download speed changed to 40M API 07-Nov-2024 10:51 </p>
                            <small class="text-muted">Hace 2 minutos</small>
                        </div>
                    </div>
                </div>
            </div> -->
            <!-- FINAL DE HISTORIAL -->
            <div class="temperaturas">
                <h2>Acciones</h2>
                <div class="item succesfuly">
                    <div class="icon">
                        <span class="material-symbols-outlined">grid_view</span>
                    </div>
                    <div class="right">
                        <div class="info">
                            <a id="botonGestion">
                                <div class="info">
                                    <h3>Gestion ONU</h3>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="item elevada">
                    <div class="icon">
                        <span class="material-symbols-outlined">restart_alt</span>
                    </div>
                    <div class="right">
                        <div class="info">
                            <a id="reiniciar" data-id="<?php echo $_GET['id'] ?>" data-metodo="reiniciar">
                                <div class="info">
                                    <h3>Reiniciar ONU</h3>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="item elevada">
                    <div class="icon">
                        <span class="material-symbols-outlined">sync</span>
                    </div>
                    <div class="right">
                        <div class="info">
                            <a id="botonResync" data-id="<?php echo $_GET['id'] ?>" data-metodo="resync">
                                <div class="info">
                                    <h3>Resincronizar ONU</h3>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="item online">
                    <div class="icon">
                        <span class="material-symbols-outlined">history</span>
                    </div>
                    <div class="right">
                        <div class="info">
                            <a id="botonRestore" data-id="<?php echo $_GET['id'] ?>" data-metodo="restore">
                                <div class="info">
                                    <h3>Restablecer por defecto</h3>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="item logs">
                    <div class="icon">
                        <span class="material-symbols-outlined">
                            receipt_long
                        </span>
                    </div>
                    <div class="right">
                        <div class="info">
                            <div class="info">
                                <button id="showLogsBtn" class="logs-btn">
                                    <h3>Mostrar Logs</h3>
                                </button>
                            </div>

                        </div>
                    </div>
                </div>
                <div id = "disableOnuFrom"class="item none" >
                </div>

                <div class="item offline">
                    <div class="icon">
                        <span class="material-symbols-outlined">delete</span>
                    </div>
                    <div class="right">
                        <a id="botonEliminar" data-id="<?php echo $_GET['id'] ?>" data-metodo="eliminar">
                            <div class="info">
                                <h3>Eliminar ONU</h3>
                            </div>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous">
        </script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/form_popup.js"></script>
    <script src="../js/refresh_onu.js"></script>
    <script src="../js/load_data_telstatus.js"></script>
    <script src="../js/theme_toggler.js"></script>
    <!-- MODALES JS FUNCIONAMIENTO -->
    <script src="../js/modales.js"></script>
    <script src="../js/modal_wifi.js"></script>
    <!-- JS PARA LAS GRAFICAS -->
    <script src="../js/graphics.js"></script>
    <!-- Modal TR069 -->
    <script src="../js/modalTR069.js"></script>
    <!-- Logs Clientes js -->
    <script src="../js/logsCliente.js"></script>

</html>