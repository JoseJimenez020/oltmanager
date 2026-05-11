$(document).ready(function () {
    var uploadTable, downloadTable;

    // Función para inicializar la tabla de Upload y llenar el tbody
    async function initializeUploadTable() {
        // Si no está inicializada la tabla, inicializarla
        if (!$.fn.DataTable.isDataTable('#speed_profileTable_upload')) {
            const searchParams = new URLSearchParams(window.location.search);
            const olt = searchParams.get('id');
            try {
                const response = await fetch(`../api/speedProfile.php?accion=allUp&olt=${olt}`);
                if (!response.ok) {
                    throw new Error(`Response status: ${response.status}`);
                  }
              
                  const data = await response.json();
                  if (data.status) {
                    // Verificamos si DataTable ya está inicializado y destruimos la instancia anterior si es necesario
                    if ($.fn.DataTable.isDataTable('#speed_profileTable_upload')) {
                        $('#speed_profileTable_upload').DataTable().destroy();  // Destruir la instancia anterior
                    }
                    // Primero, limpiar el tbody antes de insertar nuevos datos
                    $("#speedUpTable").empty();
                    // Iterar sobre los datos y agregar filas a la tabla
                    data.message.forEach(item => {

                        const row = $('<tr>');
                        row.html(`
                            <td>${item.ProfileName}</td>
                            <td>${item.IndexProfile}</td>
                            <td>${item.Speed}</td> 
                            <td>${item.Tipo}</td> 
                            <td><input type='checkbox' name='' id=''></td>
                            <td>${item.Zona}</td> 
                            <td><button class="deleteSpeedOlt" data-accion="delete" data-olt="${olt}" data-speed="${item.IdProfile}">Delete</button></td>
                            `);

                        $("#speedUpTable").append(row);
                    });

                    // Ahora inicializar DataTable solo si aún no está inicializada
                    uploadTable = $("#speed_profileTable_upload").DataTable({
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
                    console.log('No se encontraron datos para Upload');
                }
            } catch (error) {
                console.error('Error al obtener datos de Upload:', error)
            }
        }
    }

    // Función para inicializar la tabla de Download y llenar el tbody
    async function initializeDownloadTable() {

        // Si no está inicializada la tabla, inicializarla
        if (!$.fn.DataTable.isDataTable('#speed_profileTable_download')) {
            const searchParams = new URLSearchParams(window.location.search);
            const olt = searchParams.get('id');

            try {
                const response =await fetch(`../api/speedProfile.php?accion=allDown&olt=${olt}`);
                if (!response.ok) {
                    throw new Error(`Response status: ${response.status}`);
                  }
              
                  const data = await response.json();
                  if (data.status) {
                    // Verificamos si DataTable ya está inicializado y destruimos la instancia anterior si es necesario
                    if ($.fn.DataTable.isDataTable('#speed_profileTable_download')) {
                        $('#speed_profileTable_download').DataTable().destroy();  // Destruir la instancia anterior
                    }
                    // Primero, limpiar el tbody antes de insertar nuevos datos
                    $("#speedDownTable").empty();
                    // Iterar sobre los datos y agregar filas a la tabla
                    data.message.forEach(item => {

                        const row = $('<tr>');
                        row.html(`
                            <td>${item.ProfileName}</td>
                            <td>${item.IndexProfile}</td>
                            <td>${item.Speed}</td> 
                            <td>${item.Tipo}</td> 
                            <td><input type='checkbox' name='' id=''></td>
                            <td>${item.Zona}</td> 
                            <td><button class="deleteSpeedOlt" data-accion="delete" data-olt="${olt}" data-speed="${item.IdProfile}">Delete</button></td>
                            `);

                        $("#speedDownTable").append(row);
                    });

                    // Ahora inicializar DataTable solo si aún no está inicializada
                    downloadTable = $("#speed_profileTable_download").DataTable({
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
    }

    // Función para cambiar entre las pestañas de Upload y Download
    function switchTab(activeButton, inactiveButton, showContainer, hideContainer) {
        $(showContainer).show();
        $(hideContainer).hide();

        // Cambiar el estilo del botón activo
        $(activeButton).addClass("active-tab");
        $(inactiveButton).removeClass("active-tab");
    }

    // Manejar el click en el botón de Download
    $("#btnDownload").on("click", function () {
        switchTab("#btnDownload", "#btnUpload", "#downloadContainer", "#uploadContainer");
    });

    // Manejar el click en el botón de Upload
    $("#btnUpload").on("click", function () {
        switchTab("#btnUpload", "#btnDownload", "#uploadContainer", "#downloadContainer");
        initializeUploadTable();
    });

    // Mostrar Download por defecto con el color activo
    switchTab("#btnDownload", "#btnUpload", "#downloadContainer", "#uploadContainer");
    initializeDownloadTable();
});