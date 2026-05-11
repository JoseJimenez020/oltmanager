document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('#olt-table tbody');
    let dataTable;

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

    function loadTableData() {
        showLoadingIcon();

        fetch('../controllers/load_table_ok.php')
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = ''; // Limpia el contenido previo

                // Generar las filas iniciales con íconos de carga
                data.forEach(row => {
                    const statusIcon = '<span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">autorenew</span>';
                    const signalIcon = getSignalIcon(row.potencia);

                    const tableRow = `
                        <tr data-olt="${row.olt}" data-index="${row.index}">
                            <td>${statusIcon}</td>
                            <td>${row.nombre}</td>
                            <td>${row.potencia}</td>
                            <td>${signalIcon}</td>
                            <td>${row.modelo}</td>
                            <td>${row.zona}</td>
                            <td><a href="${row.ver}">Ver</a></td>
                        </tr>`;
                    tableBody.insertAdjacentHTML('beforeend', tableRow);

                    // Hacer la solicitud secundaria de manera asíncrona
                    fetch(`../controllers/load_onu.php?pass=${row.olt}&index=${row.index}`)
                        .then(response => response.json())
                        .then(statusData => {
                            const currentRow = tableBody.querySelector(`tr[data-olt="${row.olt}"][data-index="${row.index}"]`);
                            if (currentRow) {
                                const statusIcon = getStatusIcon(statusData.status);

                                // Actualizar los íconos en la fila correspondiente
                                currentRow.cells[0].innerHTML = statusData.status;
                            }
                        })
                        .catch(error => {
                            console.error(`Error al obtener datos para OLT ${row.olt}:`, error);
                            const currentRow = tableBody.querySelector(`tr[data-olt="${row.olt}"][data-index="${row.index}"]`);
                            if (currentRow) {
                                currentRow.cells[0].innerHTML = '<span class="material-symbols-outlined" style="color: red;">error</span>';
                            }
                        });
                });

                // Inicializar o reinicializar DataTables
                if ($.fn.dataTable.isDataTable('#olt-table')) {
                    dataTable.destroy();
                }
                dataTable = $('#olt-table').DataTable({
                    responsive: true,
                    pageLength: 7,
                    lengthMenu: false,
                    language: {
                        search: "Buscar",
                        info: "Mostrando _START_ a _END_ de _TOTAL_ resultados",
                        lengthMenu: ""
                    }
                });
            })
            .catch(error => {
                console.error('Error al cargar los datos:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="7" style="text-align: center; color: red;">
                            Error al cargar los datos.
                        </td>
                    </tr>`;
            });
    }

    function getStatusIcon(status) {
        switch (status) {
            case 0: return '<span class="material-symbols-outlined warning">sync_problem</span>';
            case 1: return '<span class="material-symbols-outlined">link_off</span>';
            case 2: return '<span class="material-symbols-outlined">sync</span>';
            case 3: return '<span class="material-symbols-outlined succes">public</span>';
            case 4: return '<span class="material-symbols-outlined">power_off</span>';
            case 5: return '<span class="material-symbols-outlined warning">signal_wifi_off</span>';
            case 6: return '<span class="material-symbols-outlined">public</span>';
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

    // Llamar a la función para cargar los datos al cargar la página
    loadTableData();
});
