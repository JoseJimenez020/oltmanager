<?php
require '../controllers/connection.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Tabla con Bootstrap y DataTables</title>
    <!-- Cargar los estilos solo en el iframe -->
    
    <link rel="stylesheet" href="https://cdn.datatables.net/2.1.8/css/dataTables.dataTables.css">

    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
</head>
<style>
    body{
        padding: 30px 30px;
        background: white;
    }
</style>
<body>
<div class="ONUS">
                <h2>ONU's Autorizados</h2>
                <table id="olt-table">
                    <thead>
                        <tr>
                            <th>Estado</th>
                            <th>Nombre</th>
                            <th>Modelo</th>
                            <th>Zona</th>
                            <th>Ver</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php 

                        function obtenerZona($cadena) {
                            // Primero eliminamos el prefijo "zone_" y el sufijo que comienza con "_authd" o "_descr_"
                            $cadena = preg_replace('/^zone_/', '', $cadena);  // Elimina el prefijo "zone_"
                            $cadena = preg_replace('/(_authd|_descr_).*$/', '', $cadena);  // Elimina el sufijo
                        
                            // Reemplaza los guiones bajos por espacios
                            $cadena = str_replace('_', ' ', $cadena);
                        
                            // Devuelve la cadena resultante
                            return $cadena;
                        }

                        for ($i = count($name) - 1; $i >= 0; $i--) {

                                $cadena = $desc[$i];
                    ?>
                        <tr>
                            <td><?php switch($status[$i]) {
                                        case 0: ?>
                                            <span class="material-symbols-outlined warning">sync_problem</span>
                                           <?php break;
                                        case 1: ?>
                                            <span class="material-symbols-outlined">link_off</span>
                                               <?php break;
                                        case 2: ?>
                                            <span class="material-symbols-outlined">sync</span>
                                               <?php break;
                                        case 3: ?>
                                            <span class="material-symbols-outlined succes">public</span>
                                               <?php break;
                                        case 4: ?>
                                            <span class="material-symbols-outlined">power_off</span>
                                               <?php break;
                                        case 5: ?>
                                            <span class="material-symbols-outlined warning">signal_wifi_off</span>
                                               <?php break;
                                        case 6: ?>
                                            <span class="material-symbols-outlined">public</span>
                                               <?php break;
                                    } ?> 
                            </td>
                            <td><?php echo $name[$i]; ?> </td>
                            <td><?php echo $model[$i]; ?> </td>
                            <td><?php echo obtenerZona($cadena); ?> </td>
                            <td>Ver</td>
                        </tr>
                    <?php }?>
                    </tbody>
                </table>
             </div>
    <!-- Scripts de jQuery, Bootstrap y DataTables -->
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js" integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.14.3/dist/umd/popper.min.js" integrity="sha384-ZMP7rVo3mIykV+2+9J3UJ46jBk0WLaUAdn689aCwoqbBJiSnjAK/l8WvCWPIPm49" crossorigin="anonymous"></script>
    <script src="https://cdn.datatables.net/2.1.8/js/dataTables.js"></script>
    <script>
        $(document).ready(function() {
            $('#olt-table').DataTable({
                responsive: true,
                language: {
                    search: "Buscar",
                    lengthMenu: "Mostrar _MENU_ resultados por página",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ resultados"
                }
            });
        });

    </script>
</body>
</html>
