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
    <style>
        .corte-pon {
            --cp-critico: #e6483c;
            --cp-advertencia: #e8a23c;
            --cp-inestable: #8a5cf6;
            --cp-ok: #2fa84f;
            --cp-borde: #e5e7eb;
            --cp-texto-sec: #6b7280;
            --cp-fondo-hover: #f9fafb;
            font-family: inherit;
            background: #fff;
            border: 1px solid var(--cp-borde);
            border-radius: 10px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .corte-pon__titulo {
            display: flex;
            align-items: center;
            gap: 8px;
            background: #2b2f38;
            color: #fff;
            padding: 14px 18px;
            font-size: 1.1rem;
        }

        .corte-pon__titulo .material-symbols-outlined {
            font-size: 20px;
        }

        .corte-pon__titulo small {
            margin-left: auto;
            opacity: .6;
            font-size: .75rem;
        }

        .cp-panel {
            border-bottom: 1px solid var(--cp-borde);
        }

        .cp-panel:last-child {
            border-bottom: none;
        }

        .cp-panel__header {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 18px;
            cursor: pointer;
            user-select: none;
        }

        .cp-panel__header:hover {
            background: var(--cp-fondo-hover);
        }

        .cp-panel__chevron {
            transition: transform .15s ease;
            font-size: 18px;
            color: var(--cp-texto-sec);
        }

        .cp-panel__header.is-collapsed .cp-panel__chevron {
            transform: rotate(-90deg);
        }

        .cp-panel__icono {
            font-size: 18px;
        }

        .cp-panel__titulo {
            font-weight: 600;
            font-size: .95rem;
        }

        .cp-badge {
            display: inline-flex;
            align-items: center;
            padding: 2px 10px;
            border-radius: 999px;
            font-size: .75rem;
            font-weight: 600;
            border: 1px solid transparent;
        }

        .cp-badge--neutro {
            background: #f3f4f6;
            color: #374151;
        }

        .cp-badge--critico {
            background: #fdecea;
            color: var(--cp-critico);
            border-color: #f7c9c4;
        }

        .cp-badge--advertencia {
            background: #fdf3e2;
            color: var(--cp-advertencia);
            border-color: #f6dfb0;
        }

        .cp-badge--inestable {
            background: #f1ecfe;
            color: var(--cp-inestable);
            border-color: #d9cbfb;
        }

        .cp-panel__body {
            display: block;
        }

        .cp-panel__header.is-collapsed+.cp-panel__body {
            display: none;
        }

        .cp-tabla {
            width: 100%;
            border-collapse: collapse;
            font-size: .85rem;
        }

        .cp-tabla th {
            text-align: left;
            padding: 8px 18px;
            color: var(--cp-texto-sec);
            font-weight: 600;
            font-size: .75rem;
            border-bottom: 1px solid var(--cp-borde);
            white-space: nowrap;
        }

        .cp-tabla td {
            padding: 8px 18px;
            border-bottom: 1px solid #f1f2f4;
            vertical-align: middle;
        }

        .cp-fila-grupo {
            cursor: pointer;
        }

        .cp-fila-grupo:hover {
            background: var(--cp-fondo-hover);
        }

        .cp-fila-grupo__nombre {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .cp-dot {
            width: 9px;
            height: 9px;
            border-radius: 50%;
            flex-shrink: 0;
            display: inline-block;
        }

        .cp-dot--critico {
            background: var(--cp-critico);
        }

        .cp-dot--advertencia {
            background: var(--cp-advertencia);
        }

        .cp-dot--inestable {
            background: var(--cp-inestable);
        }

        .cp-chevron-fila {
            font-size: 16px;
            color: var(--cp-texto-sec);
            transition: transform .15s ease;
        }

        .cp-chevron-fila.is-collapsed {
            transform: rotate(-90deg);
        }

        .cp-fila-detalle-header td {
            color: var(--cp-texto-sec);
            font-size: .72rem;
            font-weight: 600;
            background: #fafafa;
        }

        .cp-fila-onu td {
            padding-left: 40px;
            color: #374151;
        }

        .cp-fila-onu a {
            color: #2563eb;
            text-decoration: none;
        }

        .cp-fila-onu a:hover {
            text-decoration: underline;
        }

        .cp-var--pos {
            color: var(--cp-advertencia);
            font-weight: 600;
        }

        .cp-var--neg {
            color: var(--cp-critico);
            font-weight: 600;
        }

        .cp-var--neutro {
            color: var(--cp-texto-sec);
        }

        .cp-vacio {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 8px;
            padding: 34px 18px;
            color: var(--cp-texto-sec);
            font-size: .85rem;
        }

        .cp-vacio .material-symbols-outlined {
            font-size: 34px;
            color: var(--cp-ok);
        }

        .cp-toggle-agrupar {
            margin-left: auto;
            display: flex;
            gap: 2px;
            background: #f3f4f6;
            border-radius: 6px;
            padding: 2px;
        }

        .cp-toggle-agrupar button {
            border: none;
            background: transparent;
            padding: 4px 12px;
            font-size: .75rem;
            font-weight: 600;
            border-radius: 5px;
            cursor: pointer;
            color: var(--cp-texto-sec);
        }

        .cp-toggle-agrupar button.is-activo {
            background: #fff;
            color: #111827;
            box-shadow: 0 1px 2px rgba(0, 0, 0, .08);
        }

        .cp-toggle-agrupar button[disabled] {
            cursor: not-allowed;
            opacity: .5;
        }

        .cp-loading {
            padding: 18px;
            color: var(--cp-texto-sec);
            font-size: .85rem;
        }
    </style>
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


            <div class="ONUS corte-pon" id="corte-pon-root">
                <div class="corte-pon__titulo">
                    <span class="material-symbols-outlined">monitor_heart</span>
                    <span>Corte PON</span>
                    <small id="cp-actualizado">cargando...</small>
                </div>

                <!-- Variaciones de señal -->
                <div class="cp-panel" data-panel="variaciones_senal">
                    <div class="cp-panel__header" data-toggle-panel>
                        <span class="material-symbols-outlined cp-panel__chevron">expand_more</span>
                        <span class="material-symbols-outlined cp-panel__icono">ssid_chart</span>
                        <span class="cp-panel__titulo">Variaciones de señal</span>
                        <span class="cp-badge cp-badge--neutro" data-resumen="total_pons">0 PONs</span>
                        <span class="cp-badge cp-badge--critico" data-resumen="criticos">0 Crítico</span>
                        <span class="cp-badge cp-badge--advertencia" data-resumen="advertencias">0 Advertencia</span>
                        <span class="cp-badge cp-badge--inestable" data-resumen="inestables">0 Inestable</span>
                    </div>
                    <div class="cp-panel__body" data-body>
                        <div class="cp-loading">Cargando...</div>
                    </div>
                </div>

                <!-- LOS parcial -->
                <div class="cp-panel" data-panel="los_parcial">
                    <div class="cp-panel__header" data-toggle-panel>
                        <span class="material-symbols-outlined cp-panel__chevron">expand_more</span>
                        <span class="material-symbols-outlined cp-panel__icono">device_hub</span>
                        <span class="cp-panel__titulo">LOS parcial</span>
                        <span class="cp-badge cp-badge--neutro" data-resumen-los-parcial>0 PONs / 0 ONUs</span>
                        <div class="cp-toggle-agrupar" data-agrupar="los_parcial">
                            <button type="button" class="is-activo" data-modo="olt">OLT</button>
                            <button type="button" disabled
                                title="Requiere registrar NAP/ODB en la base de datos">NAP</button>
                        </div>
                    </div>
                    <div class="cp-panel__body" data-body>
                        <div class="cp-loading">Cargando...</div>
                    </div>
                </div>

                <!-- LOS total del PON -->
                <div class="cp-panel" data-panel="los_total">
                    <div class="cp-panel__header" data-toggle-panel>
                        <span class="material-symbols-outlined cp-panel__chevron">expand_more</span>
                        <span class="material-symbols-outlined cp-panel__icono">device_hub</span>
                        <span class="cp-panel__titulo">LOS total del PON</span>
                        <span class="cp-badge cp-badge--neutro" data-resumen-los-total>0 PON / 0 ONUs</span>
                    </div>
                    <div class="cp-panel__body" data-body>
                        <div class="cp-loading">Cargando...</div>
                    </div>
                </div>

                <!-- Fallo de energía -->
                <div class="cp-panel" data-panel="fallo_energia">
                    <div class="cp-panel__header" data-toggle-panel>
                        <span class="material-symbols-outlined cp-panel__chevron">expand_more</span>
                        <span class="material-symbols-outlined cp-panel__icono">bolt</span>
                        <span class="cp-panel__titulo">Fallo de energía</span>
                    </div>
                    <div class="cp-panel__body" data-body>
                        <div class="cp-loading">Cargando...</div>
                    </div>
                </div>

                <!-- Offline N/A -->
                <div class="cp-panel" data-panel="offline">
                    <div class="cp-panel__header" data-toggle-panel>
                        <span class="material-symbols-outlined cp-panel__chevron">expand_more</span>
                        <span class="material-symbols-outlined cp-panel__icono">help</span>
                        <span class="cp-panel__titulo">Offline N/A</span>
                    </div>
                    <div class="cp-panel__body" data-body>
                        <div class="cp-loading">Cargando...</div>
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
    <script src="../js/load_corte_pon.js"></script>
    <script src="../js/load_total.js"></script>
    <script src="../js/theme_toggler.js"></script>
    <script src="../js/load_temperatures.js"></script>
    <!--<script src="../js/olt_manager_front_styles"></script>-->
    <!-- JS PARA LOGS -->
    <script src="../js/logs.js"></script>
</body>

</html>