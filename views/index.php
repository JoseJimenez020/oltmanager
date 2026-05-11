<?php
require '../controllers/sesion.php';
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.2.2/css/dataTables.dataTables.css" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="../style/style.css">
    <link rel="icon" type="image/x-icon" href="../img/favicon.ico" />
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <title>Inicio OLTManager</title>
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
                </a>-->
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
            <h1>Inicio</h1>

            <!-- <div class="date">
                <input type="date">
            </div> -->

            <div class="insights">
                <div class="desautorizados">
                    <a href="unconfigured.php">
                        <span class="material-symbols-outlined">
                            contactless_off
                        </span>
                        <div class="middle">
                            <div class="left">
                                <h3>Desautorizados</h3>
                                <h1 class="total-unconf">100 ONUS</h1>
                            </div>
                            <!-- <div class="progress">
                                <svg>
                                    <circle cx='38' cy='38' r='36'></circle>
                                </svg>
                                <div class="number">
                                    <p>81%</p>
                                </div>
                            </div> -->
                        </div>
                        <!---<small class="text-muted">Ultimas 24 hrs.</small>-->
                    </a>
                </div>
                <!-- FINAL DE DESAUTORIZADOS -->
                <div class="autorizados">
                    <a href="autorizados.php">
                        <span class="material-symbols-outlined">
                            task_alt
                        </span>
                        <div class="middle">
                            <div class="left">
                                <h3>En Linea</h3>
                                <h1 class="total-ok"></h1>
                            </div>
                            <!-- <div class="progress">
                                <svg>
                                    <circle cx='38' cy='38' r='36'></circle>
                                </svg>
                                <div class="number">
                                    <p>81%</p>
                                </div>
                            </div> -->
                        </div>
                        <small id="online" class="text-muted"></small>
                    </a>
                </div>
                <!-- FINAL DE AUTORIZADOS -->
                <div class="offline">
                    <a href="offline.php">
                        <span class="material-symbols-outlined">
                            signal_disconnected
                        </span>
                        <div class="middle">
                            <div class="left">
                                <h3>Fuera de Linea</h3>
                                <h1 class="total-offline"></h1>
                            </div>
                            <!-- <div class="progress">
                                <svg>
                                    <circle cx='38' cy='38' r='36'></circle>
                                </svg>
                                <div class="number">
                                    <p>81%</p>
                                </div>
                            </div> -->
                        </div>
                        <small id="offline" class="text-muted"></small>
                    </a>
                </div>
                <!-- FINAL DE FUERA DE LINEA -->
                <div class="lowsignal">
                    <a href="lowsignal.php">
                        <span class="material-symbols-outlined">
                            signal_wifi_off
                        </span>
                        <div class="middle">
                            <div class="left">
                                <h3>Señal Debil</h3>
                                <h1 class="total-low"></h1>
                            </div>
                            <!-- <div class="progress">
                                <svg>
                                    <circle cx='38' cy='38' r='36'></circle>
                                </svg>
                                <div class="number">
                                    <p>81%</p>
                                </div>
                            </div> -->
                        </div>
                        <small id="low" class="text-muted"></small>
                    </a>
                </div>
                <!-- FINAL DE SEÑAL DEBIL -->
            </div>
            <!-- FINAL DE INSIGHTS -->

            <div class="ONUS">
                <h2>Logs</h2>
                <table id="logsTable">
                    <thead>
                        <tr>
                            <th>Usuario</th>
                            <th>Accion</th>
                            <th>OltName</th>
                            <th>Onu</th>
                            <th>IP</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                </table>
                <a href="fullLogs.php">Mostrar más</a>
            </div>

            <div class="ONUS">
                <h2>Caidas de PON</h2>
                <table id="olt-table">
                    <thead>
                        <tr>
                            <th>OLT</th>
                            <th>Tarjeta/Puerto</th>
                            <th>ONUs</th>
                            <th>LOS</th>
                            <th>Falla de energia</th>
                            <th>Fuera de linea</th>
                            <th>Posible Causa</th>
                            <th>Desde</th>
                            <th>Hace</th>
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
                        <?php echo isset($sesion) ? "<p>Bienvenido, <b>" . obtenerNombre($sesion['UsuarioNombre']) . "</b></p>" : "<p><b>Inicia sesión</b></p>" ?>
                        <small
                            class="text-muted"><?php echo isset($sesion) ? $sesion['PrivilegioNombre'] : "" ?></small>
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
             </div> FINAL DE HISTORIAL -->

            <?php include 'temperaturas.php'; ?>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
        integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous">
        </script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js"
        integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous">
        </script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script src="../js/load_table_index.js"></script>
    <script src="../js/load_total.js"></script>
    <script src="../js/theme_toggler.js"></script>
    <script src="../js/load_temperatures.js"></script>
    <!--<script src="../js/olt_manager_front_styles"></script>-->
    <!-- JS PARA LOGS -->
    <script src="../js/logs.js"></script>
</body>

</html>