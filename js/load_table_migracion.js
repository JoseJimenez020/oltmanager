$(document).ready(async function () {
    try {
        const response = await fetch('../api/migracion.php?accion=all');
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
          }
      
          const data = await response.json();
          if (data.status === false) {
            console.error('Error al recibir los datos:', data.message);
            return;
        }

        // Llenamos la tabla con los datos
        const tableBody = $('#onusTableBody');
        data.message.forEach(olt => {
            const row = $('<tr>');

            // Llenamos las celdas con la información del OLT
            row.html(`
                <td class="views_olts"><button class="migrarOnu" data-id="${olt.Id}">Migrar</button></td>
                <td>${olt.GponNuevo}</td>
                <td>${olt.Nombre}</td>
                <td>${olt.Zona}</td>
                <td>${olt.GponViejo}</td>
                <td>${olt.Fecha}</td>
                <td class="views_olts">
                    <input type="checkbox" class="migrar-checkbox" value="${olt.Id}">
                </td>
            `);

            // Añadimos la fila al cuerpo de la tabla
            tableBody.append(row);
        });

        // Ahora inicializamos DataTables después de llenar la tabla
        if ($.fn.DataTable) {
            
            $("#onusTable").DataTable({
                paging: true,
                searching: true,
                ordering: true,
                info: true,
                lengthMenu: [5, 10, 25, 50],
                pageLength: 100,
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
    } catch (error) {
        console.error('Error:', error);
    }
});
let allSelected = false;

$('#toggleSelectAll').on('click', function () {
    allSelected = !allSelected;
    $('.migrar-checkbox').prop('checked', allSelected);
});

$('#migrarOnus').on('click', async function () {
    const ids = {};
    ids['Id'] = [];
    ids['accion'] = 'migrar';
    $('.migrar-checkbox:checked').each(function () {
        ids['Id'].push($(this).val());
    });

    if (ids['Id'].length === 0) {
        alert("Debes seleccionar al menos una ONU para migrar.");
        return;
    }
    Swal.fire({
      title: 'Migrando Onus',
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
        const response = await fetch('../api/migracion.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(ids)
        });

        const result = await response.json();
        if (result.status) {
            location.reload();
        } else {
            alert("Error en la migración: " + result.message);
        }
    } catch (error) {
        console.error("Error al migrar:", error);
        alert("Ocurrió un error al migrar las ONUs.");
    }
});
$(document).ready(function () {
    $(document).on('click', '.migrarOnu', async function () {
        const id = {};
        id['Id'] = [];
        id['Id'].push($(this).data('id'));
        id['accion'] = 'migrar';
        Swal.fire({
          title: 'Migrando Onus',
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
            const response = await fetch('../api/migracion.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(id)
            });

            const result = await response.json();
            if (result.status) {
                location.reload();
            } else {
                alert("Error en la migración: " + result.message);
            }
        } catch (error) {
            console.error("Error al migrar:", error);
            alert("Ocurrió un error al migrar las ONUs.");
        }
    });
});
