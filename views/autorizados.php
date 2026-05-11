<?php
require '../controllers/sesion.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet">
    <!-- Agrega Select2 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../style/style.css">
  <link rel="icon" type="image/x-icon" href="../img/favicon.ico" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Autorizados</title>
</head>

<body>
    <div class="container">
        <aside>
            <div class="top">
                <div class="logo">
                    <img src="../img/logo olt-manager.png">
                </div>
                <div class="close" id="close-btn">
                    <span class="material-symbols-outlined">close</span>
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
                <a href="autorizados.php" class="active">
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
                </a> -->
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
                <h2>ONU's Autorizados</h2>
                <form id="filtrosForm">
                    <div class="container-row-one">
                        <label for="contrato">Contrato</label>
                        <input type="text" name="contrato">
                        <label for="nombre">Nombre</label>
                        <input type="text" name="nombre">
                        <label for="SN">SN</label>
                        <input type="text" name="SN">
                        <label for="OLT">OLT</label>
                        <select id ="olt"name="olt">
                        </select>
                    </div>
                    <div class="container-row-two">
                        <label for="Tarjeta">Tarjeta</label>
                        <select disabled name="tarjeta" id="tarjeta">
                            <option value="" disabled selected>Selecionar</option>
                        </select>
                        <label for="puerto">Puerto</label>
                        <select disabled name="puerto" id="puerto">
                            <option value="" disabled selected>Seleccionar</option>
                        </select>
                        <label for="status">Estado</label>
                        <select class="icon-select" name="status" id="status">
                            <option value="" selected disabled>Seleccionar</option>
                            <option value="0" data-icon="sync_problem" data-icon-class="warning">Problema de
                                sincronización</option>
                            <option value="1" data-icon="link_off">Corte de fibra</option>
                            <option value="2" data-icon="sync">Sincronizando</option>
                            <option value="3" data-icon="public" data-icon-class="succes">Conectado</option>
                            <option value="4" data-icon="power_off">Apagado</option>
                            <option value="5" data-icon="signal_wifi_off" data-icon-class="warning">Sin señal WiFi
                            </option>
                            <option value="6" data-icon="public">Fuera de linea</option>
                        </select>
                        <button type="submit">Buscar</button>
                    </div>
                </form>
                <table id="olt-table">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Nombre</th>
                            <th>Gpon</th>
                            <th>Señal</th>
                            <th>SN</th>
                            <th>Zona</th>
                            <th>Ultima Actualizacion</th>
                            <th>Ver</th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>
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
                        <?php echo isset($sesion) ? "<p>Bienvenido, <b>" . obtenerNombre($sesion['UsuarioNombre']) . "</b></p>" : "<p><b>Inicia sesión</b></p>"?>
                        <small class="text-muted"><?php echo isset($sesion) ?  $sesion['PrivilegioNombre'] : "" ?></small>
                    </div>
                    <div class="profile-photo">
                        <!-- <img src="profile-1.jpg" alt=""> -->
                    </div>
                </div>
            </div>
            <!-- FINAL DE TOP 

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
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
        crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49"
        crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="../js/load_table_ok.js"></script>
    <script src="../js/theme_toggler.js"></script>
    <!-- Select2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="../js/iconos-filtro.js"></script>
</body>

</html>