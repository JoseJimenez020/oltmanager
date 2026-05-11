$(document).ready(async function () {
    if (!$.fn.DataTable.isDataTable('#onu_typesTable')) {
        try {
            const response = await fetch('../api/onuType.php?accion=all');
            if (!response.ok) {
                throw new Error(`Response status: ${response.status}`);
              }
          
              const data = await response.json();
              if (data.status) {
                // Verificamos si DataTable ya está inicializado y destruimos la instancia anterior si es necesario
                if ($.fn.DataTable.isDataTable('#onu_typesTable')) {
                    $('#onu_typesTable').DataTable().destroy();  // Destruir la instancia anterior
                }
                // Primero, limpiar el tbody antes de insertar nuevos datos
                $("#typeTableBody").empty();
                // Iterar sobre los datos y agregar filas a la tabla
                data.message.forEach(item => {

                    const row = $('<tr>');
                    row.html(`
                        <td>${item.PonType}</td>
                        <td>${item.OnuTypeName}</td>
                        <td>${item.EthernetPorts}</td>
                        <td>${item.WifiPorts}</td>
                        <td>${item.VoipPorts}</td>
                        <td>${item.Catv}</td>
                        <!--<td>Info</td>-->
                        <td>${item.Capability}</td>
                        <td><button class="deleteBtn" data-accion="delete" data-type="${item.IdOnuType}">Delete</button></td>
                        `);

                    $("#typeTableBody").append(row);
                });

                // Ahora inicializar DataTable solo si aún no está inicializada
                downloadTable = $("#onu_typesTable").DataTable({
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
                console.log('No se encontraron datos para Download');
            }
        } catch (error) {
            console.error('Error al obtener datos de Download:', error);
        }
    }
});

// Obtener elementos
const addONUButton = document.getElementById('addONUButton'); // Botón que abre el modal
const onuFormModal = document.getElementById('onuFormModal'); // Modal del formulario
const closeFormModal = document.getElementById('closeFormModal'); // Botón de cerrar el modal
const submitFormType = document.getElementById('typeFormAdd');

// Abrir el modal cuando se hace clic en el botón "Add ONU type"
addONUButton.addEventListener('click', () => {
    onuFormModal.style.display = 'flex'; // Mostrar el modal
});

// Cerrar el modal cuando se hace clic en "Cancel"
closeFormModal.addEventListener('click', () => {
    onuFormModal.style.display = 'none'; // Ocultar el modal
});
//submit form add onuType
submitFormType.addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(submitFormType);

    Swal.fire({
        text: 'Por favor espera un momento',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => {
            Swal.showLoading();
        },
        background: '#1a1a1a',
        color: '#fff',
        customClass: {
            popup: 'my-loading-popup',
            title: 'my-loading-title',
        },
    });

    try {

        // Realizar la solicitud fetch y esperar la respuesta
        const response = await fetch("../api/onuType.php", {
            method: "POST",
            body: formData, // Los datos del formulario van en el body de la solicitud
        });

        // Verificamos si la respuesta es exitosa
        if (!response.status) {
            console.log("Error");
            throw new Error("Error en la solicitud");
        }

        // Convertimos la respuesta en formato JSON
        const data = await response.json();

        // Ejecutamos el código solo si la respuesta es exitosa
        if (data.status) {

            // Recargar la página
            location.reload();
        }
    } catch (error) {
        // Si ocurre un error, lo capturamos y mostramos un mensaje
        console.error(error);
    }
});

// También puedes cerrar el modal si se hace clic fuera del modal
window.addEventListener('click', (event) => {
    if (event.target === onuFormModal) {
        onuFormModal.style.display = 'none';
    }
});

// Obtener el modal y los botones
const modalDeleteOnuType = document.getElementById('modalDeleteonutype');
const noDeleteOnuBtn = document.getElementById('noDeleteOnuBtn');
const yesDeleteOnuBtn = document.getElementById('yesDeleteOnuBtn');
const deleteButtons = document.querySelectorAll('.deleteBtn');
const modalDeleteOnuName = document.getElementById('modalDeleteOnuName');

// Función para mostrar el modal y cambiar el nombre
deleteButtons.forEach((button) => {
    button.addEventListener('click', () => {
        modalDeleteOnuType.style.display = 'flex'; // Mostrar el modal
        const row = button.closest('tr'); // Obtener la fila más cercana al botón
        const onuName = row.cells[1].textContent; // Obtener el nombre del ONU Type
        modalDeleteOnuName.textContent = onuName; // Mostrar el nombre en el modal
    });
});

// Cerrar el modal cuando se presiona "No"
noDeleteOnuBtn.addEventListener('click', () => {
    modalDeleteOnuType.style.display = 'none'; // Cerrar el modal
});



// También cerrar el modal si se hace clic fuera del modal
window.addEventListener('click', (event) => {
    if (event.target === modalDeleteOnuType) {
        modalDeleteOnuType.style.display = 'none';
    }
});



// Delegación de eventos para el botón delete
$(document).on('click', '.deleteBtn', function () {
    var row = $(this).closest('tr');
    //typeDelete
    var id = $(this).data('type');
    var accion = $(this).data('accion');
    var onuType = row.find('td:eq(1)').text(); // Obtener el tipo ONU de la segunda columna

    $('#modalDeleteOnuName').text(onuType);
    $('#modalDeleteonutype').data('type', id).fadeIn();
    $('#modalDeleteonutype').data('accion', accion).fadeIn();
});

// Manejar el botón "No"
$('#noDeleteOnuBtn').on('click', function () {
    $('#modalDeleteonutype').fadeOut();
});

// Manejar el botón "Sí"
$('#yesDeleteOnuBtn').on('click', async function () {
    var id = $('#modalDeleteonutype').data('type');
    var accion = $('#modalDeleteonutype').data('accion');
    try {
        const Data = {
            type: id,
            accion: accion
        };

        Swal.fire({
            title: ' Eliminando',
            text: 'Por favor espera un momento',
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
                Swal.showLoading();
            },
            background: '#1a1a1a',
            color: '#fff',
            customClass: {
                popup: 'my-loading-popup',
                title: 'my-loading-title',
            },
        });

        // Realizar la solicitud fetch y esperar la respuesta
        const response = await fetch(`../api/onuType.php?type=${id}`, {
            method: "DELETE",
            headers: {
                "Content-Type": "application/x-www-form-urlencoded",
            },
        });

        // Verificamos si la respuesta es exitosa
        if (!response.status) {
            console.log("Error");
            throw new Error("Error en la solicitud");
        }

        // Convertimos la respuesta en formato JSON
        const data = await response.json();

        // Ejecutamos el código solo si la respuesta es exitosa
        if (data.status) {
            $('#modalDeleteonutype').fadeOut();
            // Recargar la página
            location.reload();
        }
    } catch (error) {
        // Si ocurre un error, lo capturamos y mostramos un mensaje
        console.error(error);
    }
});