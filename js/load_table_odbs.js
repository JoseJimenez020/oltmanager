$(document).ready(function () {
    // Inicializa DataTables
    var table = $("#odbsTable").DataTable({
        paging: true,
        searching: true,
        ordering: true,
        info: true,
        lengthMenu: [5, 10, 25, 50],
        pageLength: 5,
        language: {
            lengthMenu: "Mostrar _MENU_ registros por página",
            zeroRecords: "No se encontraron resultados",
            info: "Mostrando página _PAGE_ de _PAGES_",
            infoEmpty: "No hay registros disponibles",
            infoFiltered: "(filtrado de _MAX_ registros totales)",
            search: "Buscar:",
            paginate: {
                first: "Primero",
                last: "Último",
                next: "Siguiente",
                previous: "Anterior"
            }
        }
    });

    // Filtro personalizado para Zona (Columna 2)
    $("#zoneSearch").on("keyup", function () {
        table.column(2).search(this.value).draw();
    });

    // Filtro personalizado para OLT (Columna 3, si existe)
    $("#oltSearch").on("keyup", function () {
        table.column(3).search(this.value).draw();
    });
});