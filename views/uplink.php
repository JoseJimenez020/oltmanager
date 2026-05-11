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

        <!-- Uplink -->
        <main>
            <h1>uplink</h1>
            <div class="content" id="">
                <div class="container_settings">
                    <div class="settings_header">
                        <a href="olt_detail.php?id=<?php echo $_GET['id']; ?>"><button><img
                                    src="../img/return.png">Regresar</button></a>
                        <button>Refresh uplink ports info</button>
                    </div>

                    <table id="uplinkOltTable" class="display">
                        <thead>
                            <tr>
                                <th>Uplink port</th>
                                <th>Description</th>
                                <th>Type</th>
                                <th>Admin state</th>
                                <th>Status</th>
                                <th>Negotiation</th>
                                <th>MTU</th>
                                <th>WaveL</th>
                                <th>Temp</th>
                                <th>PVID untag</th>
                                <th>Mode: tagged VLANs</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody id="uplinkOltTableBody">
                        </tbody>
                    </table>
                </div>
            </div>
        </main>

        <!-- Modal para la configuración del uplink -->
        <div id="modalConfigureUplink" class="modal" style="display:none;">
            <div class=" modal-content">
                <h1>Configure uplink port <span id="uplinkPortName">Nombre del port</span></h1>
                <div class="container_form_settings">
                    <div class="content_form">
                        <form id="form_vlan_tagged_add">
                            <!-- Aquí tus campos del formulario -->
                            <div class="form-group">
                                <label for="">Mode</label>
                                <p>Mode trunk</p>
                            </div>
                            <div class="form-group">
                                <label for="">Tagged VLANs</label>
                                <p id="uplinkVlanTagged"></p>
                            </div>
                            <div class="form-group">
                                <label for="">Description</label>
                                <input name="desc"type="text" id="uplinkDescription">
                            </div>
                            <div class="form-group">
                                <label for="">Add VLAN</label>
                                <input name="vlanAdd"type="number" id="addVlans" placeholder="Vlan">
                            </div>
                            <div class="form-group">
                                <label for="">Remove VLANs</label>
                                <input name="vlanDelete"type="number" id="removeVlans" placeholder="Vlan">
                            </div>
                            <!--<div class="form-group">
                                <label for="">Admin state</label>
                                <div class="container_radio">
                                    <label for="enable">Enable</label>
                                    <input value="1"checked type="radio" name="adminState" id="enable">
                                    <label for="disable">Disable (Port shutdown)</label>
                                    <input value="0"type="radio" name="adminState" id="disable">
                                </div>
                            </div>-->
                            <!-- Botones -->
                            <div class="section_buttons">
                                <button type="button" id="closeConfigureModal" class="cancel-btn">
                                    <img src="../img/cancel.png" alt="Cancel">Cancel
                                </button>
                                <button type="submit" id="saveConfigureModal" class="submit-btn">
                                    <img src="../img/save.png" alt="Submit">Save
                                </button>
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
    <script src="../js/uplink_configure.js"></script>
    <script src="../js/uplink.js"></script>
</body>

</html>