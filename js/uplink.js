async function cargarTablaUplinkOlt(id) {
    try {
        const response = await fetch(`../api/uplinkPort.php?accion=All&id=${id}`);
        if (!response.ok) throw new Error(`Response status: ${response.status}`);
        const data = await response.json();
        if (!data.status) return console.warn('No se encontrato datos');

        const tabla = $('#uplinkOltTable');
        if ($.fn.DataTable.isDataTable(tabla)) {
            tabla.DataTable().destroy();
        }
        $('uplinkOltTableBody').empty();
        data.entity.forEach(item => {
            $('#uplinkOltTableBody').append(`
                <tr>
                <td>${item.port}</td>
                <td>${item.alias}</td>
                <td>${item.conntype}</td>
                <td>${item.adminstatus}</td>
                <td>${item.operstatus}</td>
                <td>${item.confduplexspeed}</td>
                <td>${item.mtu}</td>
                <td>${item.optwave}</td>
                <td>${item.opttemp}</td>
                <td></td>
                <td>${item.vlantagged}</td>
                <td>
                    <button class="configure-btn-uplink">
                        <img src="../img/gear.gif" alt="">Configure
                    </button>
                </td>
                </tr>    
            `);
        });
        tabla.DataTable({
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            lengthMenu: [5, 10, 25, 50],
            pageLength: 16,
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
        console.error('Error al cargar uplink:', error);
    }
}
$(document).ready(function () {
    const searchParams = new URLSearchParams(window.location.search);
    let id = searchParams.get('id');
    cargarTablaUplinkOlt(id);
});

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('modalConfigureUplink');
    const closeModalButton = document.getElementById('closeConfigureModal');
    const formVlanTagged = document.getElementById('form_vlan_tagged_add');
    formVlanTagged.addEventListener('submit', function (e) {
        e.preventDefault();
        const formData = new FormData(formVlanTagged);

        console.log(Object.fromEntries(formData));
    });
    document.querySelector('.container').addEventListener('click', function (event) {
        const button = event.target.closest('.configure-btn-uplink');
        if (button) {
            const row = button.closest('tr');
            const cells = row.querySelectorAll('td');

            const vlanTagged = cells[10].textContent.trim();
            const uplinkPortName = cells[0].textContent.trim();
            const uplinkDesc = cells[1].textContent.trim();
            
            document.getElementById('uplinkVlanTagged').textContent = vlanTagged;
            document.getElementById('uplinkPortName').textContent = uplinkPortName;
            document.getElementById('uplinkDescription').value = uplinkDesc;
            modal.style.display = 'flex';
        }
    });
    closeModalButton.addEventListener('click', function () {
        modal.style.display = 'none';
    });
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});


