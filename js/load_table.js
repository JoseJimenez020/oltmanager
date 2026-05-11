document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('#olt-table tbody');

    // Muestra un icono de carga mientras se cargan los datos
    function showLoadingIcon() {
        tableBody.innerHTML = `
            <tr>
                <td colspan="5" style="text-align: center;">
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
    
        fetch('controllers/load_database.php')
            .then(response => response.json())
            .then(data => {
                tableBody.innerHTML = ''; // Limpia el contenido previo
    
                // Genera las filas de la tabla
                const rows = data.map(row => {
                    const statusIcon = getStatusIcon(row.status);
                    return `
                        <tr>
                            <td>${statusIcon}</td>
                            <td>${row.nombre}</td>
                            <td>${row.modelo}</td>
                            <td>${row.zona}</td>
                            <td><a href="${row.ver}">Ver</a></td>
                        </tr>`;
                });
    
                // Inserta las filas en el cuerpo de la tabla
                tableBody.innerHTML = rows.join('');
    
                // Destruye la instancia previa de DataTables y la vuelve a inicializar
                if ($.fn.dataTable.isDataTable('#olt-table')) {
                    dataTable.destroy();
                }
                dataTable = $('#olt-table').DataTable({
                    responsive: true,
                    pageLength: 2,
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
                        <td colspan="5" style="text-align: center; color: red;">
                            Error al cargar los datos.
                        </td>
                    </tr>`;
            });
    }
    

    // Devuelve el icono basado en el estado
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

    // Llamar a la función para cargar los datos al cargar la página
    loadTableData();
});
