document.addEventListener('DOMContentLoaded', function () {
    // Obtener elementos del DOM
    const showLogsBtn = document.getElementById('showLogsBtn');
    const logsContainer = document.getElementById('logsContainer');
    let dataTable = null;
    let logsLoaded = false;

    // Obtener el ID de la ONU de la URL
    const urlParams = new URLSearchParams(window.location.search);
    const onuId = urlParams.get('id');

    // Configurar el botón
    showLogsBtn.addEventListener('click', function () {
        if (logsContainer.style.display === 'none') {
            // Mostrar contenedor
            logsContainer.style.display = 'block';

            // Cambiar texto del botón
            showLogsBtn.querySelector('h3').textContent = 'Ocultar Logs';

            // Cargar datos solo la primera vez
            if (!logsLoaded) {
                loadAndDisplayLogs();
                logsLoaded = true;
            }
        } else {
            // Ocultar contenedor
            logsContainer.style.display = 'none';

            // Cambiar texto del botón
            showLogsBtn.querySelector('h3').textContent = 'Mostrar Logs';
        }
    });

    // Función para cargar y mostrar logs
    async function loadAndDisplayLogs() {
        if (!onuId) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se encontró ID de ONU en la URL'
            });
            return;
        }

        // Mostrar loader
        Swal.fire({
            title: 'Cargando logs...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        try {
            const response = await fetch(`../api/logs.php?accion=onu&onu=${onuId}`);
            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
              }
          
              const json = await response.json();
              Swal.close();

                if (json.status && json.message) {
                    // Ordenar logs por fecha (más reciente primero)
                    const logsOrdenados = json.message.sort((a, b) => {
                        return new Date(b.Date) - new Date(a.Date);
                    });

                    // Tomar solo los 5 más recientes
                    const ultimos5Logs = logsOrdenados.slice(0, 5);

                    // Inicializar DataTable si no existe
                    if (!dataTable) {
                        dataTable = $('#logsOntTable').DataTable({
                            "data": ultimos5Logs,
                            "columns": [
                                { "data": "UsuarioCorreo", "className": "dt-center" },
                                { "data": "Accion", "className": "dt-center" },
                                { "data": "OltName", "className": "dt-center" },
                                { "data": "Onu", "className": "dt-center" },
                                { "data": "Ip", "className": "dt-center" },
                                {
                                    "data": "Date",
                                    "className": "dt-center",
                                    "render": formatDate
                                }
                            ],
                            "paging": false,
                            "searching": false,
                            "info": false,
                            "ordering": false,
                            "dom": 't'
                        });
                    } else {
                        // Si ya existe, solo actualizar los datos
                        dataTable.clear().rows.add(ultimos5Logs).draw();
                    }
                } else {
                    throw new Error('Estructura de respuesta inválida');
                }
        } catch (error) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los logs: ' + error.statusText
            });
            console.error('Error al cargar logs:', error);
        }
    }

    // Función para formatear fechas
    function formatDate(dateString) {
        if (!dateString) return 'N/A';

        const options = {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit'
        };
        return new Date(dateString).toLocaleDateString('es-ES', options);
    }
});