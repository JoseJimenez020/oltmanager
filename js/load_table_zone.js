// ZONE DATA TABLE
$(document).ready(function () {
    if ($.fn.DataTable) {
        console.log("ZONE INCIALIZA");
        $("#zonesTable").DataTable({
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
    } else {
        console.error("Error: DataTables no está cargado.");
    }
});