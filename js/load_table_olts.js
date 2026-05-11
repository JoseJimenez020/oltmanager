$(document).ready(async function () {
    try {
        const response = await fetch('../api/oltProfile.php?accion=all');
        if (!response.ok) {
            throw new Error(`Response status: ${response.status}`);
          }
      
          const data = await response.json();
          if (data.status === false) {
            console.error('Error al recibir los datos:', data.message);
            return;
        }

        // Llenamos la tabla con los datos
        const tableBody = $('#oltTableBody');
        data.message.forEach(olt => {
            const row = $('<tr>');

            // Llenamos las celdas con la información del OLT
            row.html(`
                <td class="views_olts"><a href="olt_detail.php?id=${olt.OltIdApi}"><button>View</button></a></td>
                <td>${olt.OltIdApi}</td>
                <td>${olt.OltName}</td>
                <td>${olt.OltIpPrivate}</td>
                <td>${olt.OltTelnetPort}</td>
                <td>no</td>
                <td>${olt.OltHardVer}</td>
                <td>${olt.SoftVer}</td>
                 <!-- class="action_olts">
                    <button class="action_olts_disable" onclick="disableOlt(${olt.OltIdApi})">Disable</button>
                    <button class="action_olts_delete" onclick="deleteOlt(${olt.OltIdApi})">Delete</button>
                </td>
                -->
            `);

            // Añadimos la fila al cuerpo de la tabla
            tableBody.append(row);
        });

        // Ahora inicializamos DataTables después de llenar la tabla
        if ($.fn.DataTable) {
            
            $("#oltsTable").DataTable({
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
    } catch (error) {
        console.error('Error:', error);
    }
});
const upOnuList = document.getElementById('updateOnuList');

upOnuList.addEventListener('click', async function(){
    Swal.fire({
      title: 'Procesando datos',
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
        const data = {
            'accion':'inOnuOlt'
        };
        const response = await fetch('../api/oltProfile.php',{
            method:'POST',
            headers:{
                'Content-Type':'application/json',
            },
            body: JSON.stringify(data),
        });
        if (!response.ok) {
            throw new Error(`Response: ${response.status}`);
        }      
        const json = await response.json();
        if (json.status) {
            location.reload();
        }
    } catch (error) {
        console.error(error);
    }
});