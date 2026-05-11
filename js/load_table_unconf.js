document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('#olt-table tbody');

    // Muestra un icono de carga mientras se cargan los datos
    function showLoadingIcon() {
        tableBody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center;">
                    <div class="loading-icon">
                        <span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">
                            autorenew
                        </span>
                    </div>
                </td>
            </tr>`;
    }

    async function loadTableData() {
        showLoadingIcon();

        try {
            const response = await fetch('../controllers/load_table_unconf.php');
            const data = await response.json();
            tableBody.innerHTML = ''; // Limpia el contenido previo

            // Genera las filas de la tabla
            const rows = data.map((row, index) => `
                <tr data-index="${index}">
                    <td>${row.zona}</td>
                    <td>${row.serie}</td>
                    <td>${row.tipo}</td>
                    <td>${row.version}</td>
                    <td><a href="${row.ver}">Autorizar</a></td>
                </tr>`);

            // Inserta las filas en el cuerpo de la tabla
            tableBody.innerHTML = rows.join('');

            // Destruye la instancia previa de DataTables y la vuelve a inicializar
            //if ($.fn.dataTable.isDataTable('#olt-table')) {
            //    dataTable.destroy();
            //}
            const dataTable = $('#olt-table').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: false,
                deferRender: false,
                language: {
                    search: "Buscar",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ resultados",
                    lengthMenu: ""
                },
                drawCallback: function () {
                    //updateVisibleRows(data);
                }
            });

            // Realizar las solicitudes adicionales secuencialmente
            //for (let i = 0; i < data.length; i++) {
            //    await sendAdditionalRequest(data[i].olt, data[i].index, i);
            //}
        } catch (error) {
            console.error('Error al cargar los datos:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; color: red;">
                        Error al cargar los datos.
                    </td>
                </tr>`;
        }
    }

    // Actualiza las filas visibles en la tabla
    function updateVisibleRows(data) {
        const rows = document.querySelectorAll('#olt-table tbody tr');
        rows.forEach((row) => {
            const rowIndex = row.getAttribute('data-index');
            if (data[rowIndex]) {
                sendAdditionalRequest(data[rowIndex].olt, data[rowIndex].index, rowIndex);
            }
        });
    }

    // Realiza una solicitud adicional con los datos obtenidos
    async function sendAdditionalRequest(olt, index, rowIndex) {
        try {
            const response = await fetch(`../controllers/olt-conn.php?pass=${olt}&index=${index}`);
            const result = await response.json();

            const statusIconHtml = getStatusIcon(result.status);
            const potenciaIconHtml = getSignalIcon(result.potencia);
            const row = tableBody.querySelector(`tr[data-index="${rowIndex}"]`);
            if (row) {
                const statusCell = row.querySelector('.status-icon-placeholder');
                const signalIcon = row.querySelector('.signal-icon');

                // Actualizar celdas con los resultados
                if (statusCell) statusCell.innerHTML = statusIconHtml;
                if (signalIcon) signalIcon.innerHTML = potenciaIconHtml;
            }
        } catch (error) {
            console.error(`Error en la solicitud adicional para OLT ${olt}, índice ${index}:`, error);
        }
    }

    // Devuelve el icono basado en el estado
    function getStatusIcon(status) {
        switch (status) {
            case "0": return '<span class="material-symbols-outlined warning">sync_problem</span>';
            case "1": return '<span class="material-symbols-outlined">link_off</span>';
            case "2": return '<span class="material-symbols-outlined">sync</span>';
            case "3": return '<span class="material-symbols-outlined succes">public</span>';
            case "4": return '<span class="material-symbols-outlined">power_off</span>';
            case "5": return '<span class="material-symbols-outlined warning">signal_wifi_off</span>';
            case "6": return '<span class="material-symbols-outlined">public</span>';
            default: return '<span class="material-symbols-outlined">help</span>';
        }
    }

    function getSignalIcon(potencia) {
        if (potencia > -30.00) {
            return '<span class="material-symbols-outlined succes">signal_cellular_alt</span>';
        } else if (potencia <= -80.00) {
            return '-';
        } else if ((potencia <= -30.00) && (potencia > -32.00)) {
            return '<span class="material-symbols-outlined warning">signal_cellular_alt</span>';
        } else if (potencia <= -32.00) {
            return '<span class="material-symbols-outlined danger">signal_cellular_alt</span>';
        }
    }

    /*function getOltFormat(olt){
        switch (olt) {
            case "cardenasChihuahua": return "Cardenas Chihuahua";
            case "central": return "Central";
            case "meoqui": return "Meoqui";
            case "allende": return "Allende";
            case "parrilla": return "parrilla";
            case "campestre": return "Campestre";
            case "pichucalco": return "Pichucalco";
            case "cdDelCarmen": return "Cd del Carmen";
            case "nacajuca": return "Nacajuca";
            case "deliciasChihuahua": return "Delicias Chihuahua";
            case "tacotalpa": return "Tacotalpa";
            case "paraiso": return "Paraiso";
            case "jalapa": return "Jalapa";
            case "Teapa": return "Teapa";
            case "comalcalco": return "Comalcalco";
            case "pomoca": return "Pomoca";
            case "jalpa": return "Jalpa";
            case "cunduacan": return "Cunduacan";
            default: return "-";    
        } 

    }*/

    // Llamar a la función para cargar los datos al cargar la página
    loadTableData();
});