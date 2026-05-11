document.addEventListener('DOMContentLoaded', function () {
    // Función para cargar todos los logs
    function loadAllLogs() {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: '../api/logs.php?accion=general',
                type: 'GET',
                dataType: 'json',
                success: function (response) {
                    if (response.status && response.message) {
                        resolve(response.message);
                    } else {
                        reject(new Error('Estructura de respuesta inválida'));
                    }
                },
                error: function (xhr, status, error) {
                    reject(error);
                }
            });
        });
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

    // Mostrar loader
    function showLoader() {
        Swal.fire({
            title: 'Cargando logs',
            html: 'Por favor espere...',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
    }

    // Inicializar DataTable
    function initializeDataTable(logs) {
        $('#logsDataTable').DataTable({
            "data": logs,
            "columns": [
                {
                    "data": "UsuarioCorreo",
                    "defaultContent": "N/A",
                    "render": function (data) {
                        return data || 'N/A';
                    }
                },
                {
                    "data": "Accion",
                    "defaultContent": "N/A",
                    "render": function (data) {
                        return data || 'N/A';
                    }
                },
                {
                    "data": "OltName",
                    "defaultContent": "N/A",
                    "render": function (data) {
                        return data || 'N/A';
                    }
                },
                {
                    "data": "Onu",
                    "defaultContent": "N/A",
                    "render": function (data) {
                        return data || 'N/A';
                    }
                },
                {
                    "data": "Ip",
                    "defaultContent": "N/A",
                    "render": function (data) {
                        return data || 'N/A';
                    }
                },
                {
                    "data": "Date",
                    "defaultContent": "N/A",
                    "render": function (data, type) {
                        if (type === 'display' || type === 'filter') {
                            return formatDate(data);
                        }
                        return data;
                    }
                }
            ],
            "pageLength": 20,
            "lengthMenu": [[10, 20, 50, 100], [10, 20, 50, 100]],
            "language": {
                "zeroRecords": "No se encontraron registros",
                "info": "Mostrando página _PAGE_ de _PAGES_",
                "infoEmpty": "No hay registros disponibles",
                "infoFiltered": "(filtrado de _MAX_ registros totales)",
                "loadingRecords": "Cargando...",
                "paginate": {
                    "first": "Primera",
                    "last": "Última",
                    "next": "Siguiente",
                    "previous": "Anterior"
                }
            },
            "order": [[5, "desc"]],
            "responsive": true,
            "dom": 'rt<"bottom"ip><"clear">', // Cambio 1: Eliminado el filtro de búsqueda
            "initComplete": function () {
                Swal.close();
                // Cambio 2: Eliminada la línea que configuraba el placeholder del buscador
            },
            "drawCallback": function () {
                // Personalización adicional después de dibujar la tabla
            }
        });
    }

    // Cargar datos e inicializar tabla
    showLoader();
    loadAllLogs()
        .then(logs => {
            // Ordenar logs por fecha descendente antes de mostrarlos
            logs.sort((a, b) => new Date(b.Date) - new Date(a.Date));
            initializeDataTable(logs);

            // Actualizar cada 60 segundos
            setInterval(() => {
                loadAllLogs()
                    .then(newLogs => {
                        const table = $('#logsDataTable').DataTable();
                        newLogs.sort((a, b) => new Date(b.Date) - new Date(a.Date));
                        table.clear().rows.add(newLogs).draw();
                    })
                    .catch(error => {
                        console.error('Error al actualizar logs:', error);
                    });
            }, 60000);
        })
        .catch(error => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'No se pudieron cargar los logs: ' + error.message
            });
            console.error('Error:', error);
        });
});