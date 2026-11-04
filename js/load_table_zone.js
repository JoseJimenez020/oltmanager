$(document).ready(async function () {
    const esc = t => $('<div>').text(t ?? '').html();
    try {
        const response = await fetch('../api/zonas.php');
        if (!response.ok) throw new Error(`Response status: ${response.status}`);
        const data = await response.json();
        if (!data.status) throw new Error(data.message);

        $('#zonesTableBody').html(data.message.map(z => `
            <tr>
                <td>${esc(z.nombre)}</td>
                <td>${z.total}</td>
            </tr>`).join(''));

        $('#zonesTable').DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthMenu: [5, 10, 25, 50, 100],
            pageLength: 10,
            language: {
                lengthMenu: "Mostrar _MENU_ registros por página",
                zeroRecords: "No se encontraron resultados",
                info: "Mostrando página _PAGE_ de _PAGES_",
                infoEmpty: "No hay registros disponibles",
                infoFiltered: "(filtrado de _MAX_ registros totales)",
                search: "Buscar:",
                paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
            }
        });
    } catch (error) {
        console.error('Error al cargar zonas:', error);
    }
});