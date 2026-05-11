document.addEventListener("DOMContentLoaded", function () {
  const tableBody = document.querySelector("#olt-table tbody");

  // Muestra un icono de carga mientras se cargan los datos
  function showLoadingIcon() {
    tableBody.innerHTML = `
            <tr>
                <td colspan="7" style="text-align: center;">
                    <div class="loading-icon">
                        <span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">
                            autorenew
                        </span>
                    </div>
                </td>
            </tr>`;
  }

  async function loadTableData() {
    showLoadingIcon();

    try {
      const response = await fetch("../controllers/outage.php");
      const data = await response.json();
      tableBody.innerHTML = ""; // Limpia el contenido previo

      // Genera las filas de la tabla
      const rows = data.map(
        (row, index) => `
                <tr data-index="${index}">
                    <td>${row.olt}</td>
                    <td>${row.gpon}</td>
                    <td>${row.onus}</td>
                    <td>${row.los}</td>
                    <td>${row.pfail}</td>
                    <td>${row.offline}</td>
                    <td>${row.mensaje}</td>
                    <td>${row.desde}</td>
                    <td>${row.hace}</td>
                </tr>`
      );

      // Inserta las filas en el cuerpo de la tabla
      tableBody.innerHTML = rows.join("");

    } catch (error) {
      console.error("Error al cargar los datos:", error);
      tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; color: red;">
                        Error al cargar los datos.
                    </td>
                </tr>`;
    }
  }

  // Llamar a la función para cargar los datos al cargar la página
  loadTableData();

});

