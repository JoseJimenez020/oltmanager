$(document).ready(async function () {
  const esc = t => $('<div>').text(t ?? '').html();
  let table;

  async function cargar() {
    const r = await fetch('../api/usuarios.php');
    const d = await r.json();
    if (!d.status) { Swal.fire('Error', d.message, 'error'); return; }

    if ($.fn.DataTable.isDataTable('#oltsgeneralTable')) {
      $('#oltsgeneralTable').DataTable().destroy();
    }
    $('#usersTableBody').html(d.message.map(u => `
      <tr>
        <td>${esc(u.UsuarioNombre)}</td>
        <td>${esc(u.UsuarioCorreo)}</td>
        <td>${esc(u.UsuarioTelefono)}</td>
        <td>${esc(u.PrivilegioNombre)}</td>
        <td>
          <button class="btn-edit-user" data-user='${esc(JSON.stringify(u)).replace(/'/g, "&#39;")}'>Editar</button>
          <button class="btn-del-user" data-id="${u.UsuarioId}" data-name="${esc(u.UsuarioNombre)}">Eliminar</button>
        </td>
      </tr>`).join(''));
    table = $('#oltsgeneralTable').DataTable();
  }

  // Delegación: las filas se crean dinámicamente
  $(document).on('click', '.btn-edit-user', async function () {
    const u = JSON.parse($(this).attr('data-user'));
    const { value: f } = await Swal.fire({
      title: 'Editar usuario',
      html: `
        <input id="e-nombre" class="swal2-input" placeholder="Nombre" value="${esc(u.UsuarioNombre)}">
        <input id="e-correo" class="swal2-input" placeholder="Correo" value="${esc(u.UsuarioCorreo)}">
        <input id="e-tel" class="swal2-input" placeholder="Teléfono" value="${esc(u.UsuarioTelefono)}">
        <select id="e-priv" class="swal2-input">
          <option value="1" ${u.UsuarioPrivilegios == 1 ? 'selected' : ''}>Administrador</option>
          <option value="2" ${u.UsuarioPrivilegios == 2 ? 'selected' : ''}>Solo Leer</option>
          <option value="3" ${u.UsuarioPrivilegios == 3 ? 'selected' : ''}>Call Center</option>
        </select>
        <input id="e-pass" type="password" class="swal2-input" placeholder="Nueva contraseña (opcional)">`,
      showCancelButton: true, confirmButtonText: 'Guardar',
      preConfirm: () => ({
        id: u.UsuarioId,
        nombre: $('#e-nombre').val().trim(),
        correo: $('#e-correo').val().trim(),
        telefono: $('#e-tel').val().trim(),
        privilegio: $('#e-priv').val(),
        password: $('#e-pass').val()
      })
    });
    if (!f) return;
    const r = await fetch('../api/usuarios.php', {
      method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(f)
    });
    const d = await r.json();
    Swal.fire(d.status ? 'Listo' : 'Error', d.message, d.status ? 'success' : 'error');
    if (d.status) cargar();
  });

  $(document).on('click', '.btn-del-user', async function () {
    const ok = await Swal.fire({
      title: `¿Eliminar a ${$(this).data('name')}?`, icon: 'warning',
      showCancelButton: true, confirmButtonText: 'Sí, eliminar'
    });
    if (!ok.isConfirmed) return;
    const r = await fetch('../api/usuarios.php', {
      method: 'DELETE', headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id: $(this).data('id') })
    });
    const d = await r.json();
    Swal.fire(d.status ? 'Listo' : 'Error', d.message, d.status ? 'success' : 'error');
    if (d.status) cargar();
  });

  cargar();
});