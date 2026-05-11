async function cargarTablaVlansOlt(id) {
    try {
        const response = await fetch(`../api/vlanProfile.php?accion=All&id=${id}`);
        if (!response.ok) throw new Error(`Response status: ${response.status}`);

        const data = await response.json();
        if (!data.status) return console.warn('No se encontraron datos');

        // Destruir instancia previa de DataTable si existe
        const $tabla = $('#vlansOltTable');
        if ($.fn.DataTable.isDataTable($tabla)) {
            $tabla.DataTable().destroy();
        }

        // Limpiar cuerpo de la tabla
        $("#vlansOltTableBody").empty();

        // Insertar filas
        data.entity.forEach(item => {
            $("#vlansOltTableBody").append(`
                <tr>
                    <td>${item.Vlan}</td>
                    <td>${item.Desc}</td>
                    <td>${item.Scope}</td>
                    <td>${item.Total}</td>
                    <td><button class="deleteBtn" data-accion="deleteVlan" data-id="${item.Id}">Delete</button></td>
                </tr>
            `);
        });

        // Inicializar DataTable
        $tabla.DataTable({
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

    } catch (error) {
        console.error('Error al cargar tipos de ONU:', error);
    }
}
$(document).ready(function () {
    let deleteId = null;
    let accion = null;
    const searchParams = new URLSearchParams(window.location.search);
    let id = searchParams.get('id');
    cargarTablaVlansOlt(id);
    $(document).on('click', '.deleteBtn', function () {
        accion = $(this).data('accion');
        deleteId = $(this).data("id");
        $("#modalDeleteZoneName").text(deleteId);
        $("#modalDeleteZone").fadeIn();
    });
    $("#noDeleteZoneBtn, .close, #modalDeleteZone").on("click", function (event) {
        if (
            event.target === $("#modalDeleteZone")[0] ||
            $(event.target).hasClass("no-btn") ||
            $(event.target).hasClass("close")
        ) {
            $("#modalDeleteZone").fadeOut();
        }
    });
    $("#yesDeleteZoneBtn").on("click", async function () {
        $("#modalDeleteZone").fadeOut();
        Swal.fire({
            title: 'Cargando...',
            text: 'Por favor espera...',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });
        try {
            let form = {
                'accion': accion,
                'id': deleteId
            };
            const response = await fetch('../api/vlanProfile.php', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(form)
            });
            if (!response.ok) {
                console.log('error');
            }
            const data = await response.json();
            Swal.close();
            if (data.status) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'La operación se realizó correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    background: '#1a1a1a',
                    color: '#fff',
                });
                cargarTablaVlansOlt(id);
            } else {
                Swal.fire({
                    title: 'Error',
                    text: `No se pudo completar la operación ${data.error}`,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                });
            }
        } catch (error) {
            Swal.close();
            Swal.fire({
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor.',
                icon: 'error',
                confirmButtonColor: '#d33',
            });
        }
    });
    //form
    $("#button_vlan_olt_open").on('click', function () {
        $("#form_vlan_olt_add").fadeIn();
    });
    $("#closeFormModal").on('click', function () {
        $("#form_vlan_olt_add").fadeOut();
    });
    $("#form_vlan_olt_add").on("click", function (e) {
        if ($(e.target).is("#form_vlan_olt_add")) {
            $(this).fadeOut();
        }
    });
    $('#form_vlan_add').on('submit', async function (e) {
        e.preventDefault();
        const form = this;
        const formData = new FormData(form);
        try {
            Swal.fire({
                title: 'Cargando...',
                text: 'Por favor espera...',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            const response = await fetch('../api/vlanProfile.php', {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(Object.fromEntries(formData))
            });
            if (!response.ok) {
                console.log('error');
            }
            const data = await response.json();
            Swal.close();
            if (data.status) {
                Swal.fire({
                    title: '¡Éxito!',
                    text: 'La operación se realizó correctamente.',
                    icon: 'success',
                    confirmButtonColor: '#28a745',
                    background: '#1a1a1a',
                    color: '#fff',
                });
                cargarTablaVlansOlt(id);
            } else {
                Swal.fire({
                    title: 'Error',
                    text: `No se pudo completar la operación ${data.error}`,
                    icon: 'error',
                    confirmButtonColor: '#d33',
                });
            }
        } catch (error) {
            Swal.close();
            Swal.fire({
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor.',
                icon: 'error',
                confirmButtonColor: '#d33',
            });
        }
    });
});
