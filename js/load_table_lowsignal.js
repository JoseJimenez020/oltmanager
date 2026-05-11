document.addEventListener('DOMContentLoaded', function () {
    const tableBody = document.querySelector('#olt-table tbody');
    let dataTable;

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
            const response = await fetch('../controllers/load_table_low.php');
            const data = await response.json();
            tableBody.innerHTML = ''; // Limpia el contenido previo

            // Genera las filas de la tabla
            const rows = data.map((row, index) => `
                <tr data-index="${index}">
                    <td>${getStatusIcon(row.status)}</td>
                    <td>${row.nombre}</td>
                    <td>${row.potencia}</td>
                    <td>${getSignalIcon(row.potencia)}</td>
                    <td>${row.serie}</td>
                    <td>${row.gpon}</td>
                    <td>${row.zona}</td>
                    <td><a href="${row.ver}">Ver</a></td>
                </tr>`);

            // Inserta las filas en el cuerpo de la tabla
            tableBody.innerHTML = rows.join('');

            // Destruye la instancia previa de DataTables y la vuelve a inicializar
            if ($.fn.dataTable.isDataTable('#olt-table')) {
                dataTable.destroy();
            }
            dataTable = $('#olt-table').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: false,
                deferRender: false,
                searching:false,
                language: {
                    search: "Buscar",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ resultados",
                    lengthMenu: ""
                }
            });
        } catch (error) {
            console.error('Error al cargar los datos:', error);
            tableBody.innerHTML = `
                <tr>
                    <td colspan="7" style="text-align: center; color: red;">
                        Error al cargar los datos.
                    </td>
                </tr>`;
        }
    }

    // Devuelve el icono basado en el estado
    function getStatusIcon(status) {
        switch (status) {
            case 0: return '<span class="material-symbols-outlined warning">sync_problem</span>';
            case 1: return '<span class="material-symbols-outlined">link_off</span>';
            case 2: return '<span class="material-symbols-outlined">sync</span>';
            case 3: return '<span class="material-symbols-outlined succes">public</span>';
            case 4: return '<span class="material-symbols-outlined">power_off</span>';
            case 5: return '<span class="material-symbols-outlined warning">signal_wifi_off</span>';
            case 6: return '<span class="material-symbols-outlined">public</span>';
            default: return '<span class="material-symbols-outlined">help</span>';
        }
    }

    function getSignalIcon(potencia) {
        if (potencia > -30.00) {
            return '<span class="material-symbols-outlined succes">signal_cellular_alt</span>';
        } else if (potencia <= -80.00) {
            return '-';
        } else if ((potencia <= -30.00) && (potencia > -32.00)) {
            return '<span class="material-symbols-outlined warning">signal_cellular_alt</span>';
        } else if (potencia <= -32.00) {
            return '<span class="material-symbols-outlined danger">signal_cellular_alt</span>';
        }
    }
    async function oltList(){
        try {
          const response = await fetch(`../api/oltProfile.php?accion=listName`);
          const data = await response.json();
          if (!data.status) {
              console.error("Error desde el servidor");
              return;
          }
          if (data.status && Array.isArray(data.olt)) {
            const select = document.querySelector("#filtrosForm #olt");
            select.innerHTML = "";
            data.olt.forEach(item => {
                const option = document.createElement("option");
                option.value = item.OltIdApi;
                option.textContent = item.OltName;
                select.appendChild(option);
            });
            let option = document.createElement("option");
            option.value = '';
            option.textContent = 'Todos';
            option.selected = true;
            select.appendChild(option);
          } else {
              console.error("Respuesta inesperada del servidor:", data);
          }
        } catch (error) {
            console.error("Error al cargar los datos de la ONU:", error);
        }
      }
      oltList();
    // Llamar a la función para cargar los datos al cargar la página
    loadTableData();

    const olt = document.getElementById('olt');
    const tarjeta = document.getElementById('tarjeta');
    const puerto = document.getElementById('puerto');
  
    olt.addEventListener("input", function () {
      if (olt.value.trim() !== "") {
        fetch(`../controllers/tarjeta_gpon.php?olt=${olt.value}`)
          .then(response => response.json())
          .then(data => {
  
            tarjeta.disabled = false;
            tarjeta.innerHTML = `<option value="" selected>Seleccionar</option>`;
          
            data.forEach(row => {
              const option = document.createElement("option");
              option.value = row.tarjeta;
              option.textContent = row.tarjeta;
              tarjeta.appendChild(option);
          });
          
          puerto.disabled = true;
          puerto.innerHTML = '<option value="" disabled selected>Selecciona una tarjeta</option>';
      })
      .catch(error => console.error("Error:", error));
      } else {
        tarjeta.innerHTML = '<option value="" disabled selected>Slecciona una Olt</option>'
        tarjeta.disabled = true;
  
        puerto.innerHTML = '<option value="" disabled selected>Selecciona una tarjeta</option>'
        puerto.disabled = true;
      }
  });
  
  tarjeta.addEventListener("input", function () {
    if (tarjeta.value.trim() !== "") {
      fetch(`../controllers/puerto_gpon.php?olt=${olt.value}&card=${tarjeta.value}`)
        .then(response => response.json())
        .then(data => {
  
          puerto.disabled = false;
          puerto.innerHTML = `<option value="" selected>Seleccionar</option>`;
        
          data.forEach(row => {
            const option = document.createElement("option");
            option.value = row.puerto;
            option.textContent = row.puerto;
            puerto.appendChild(option);
        });
        
    })
    .catch(error => console.error("Error:", error));
    } else {
      puerto.innerHTML = '<option value="" disabled selected>Selecciona una tarjeta</option>'
      puerto.disabled = true;
    }
  });
  
    document.getElementById("filtrosForm").addEventListener("submit", function (event) {
      event.preventDefault(); // Evitar recarga de página
  
      showLoadingIcon();
    
      let formData = new FormData(this); // Obtener los datos del formulario
      let filtros = {};
    
      formData.forEach((value, key) => {
          if (value.trim() !== "") {
              filtros[key] = value;
          }
      });
    
      fetch("../controllers/filtros_low.php", {
          method: "POST",
          body: JSON.stringify(filtros),
          headers: {
              "Content-Type": "application/json"
          }
      })
      .then(response => response.json())
      .then(data => {
  
        if ($.fn.dataTable.isDataTable('#olt-table')) {
          dataTable.destroy();
        }
  
        tableBody.innerHTML = "";
  
        const rows = data.map(row => {
          return `
            <tr>
                <td>${getStatusIcon(row.status)}</td>
                <td>${row.nombre}</td>
                <td>${row.potencia}</td>
                <td>${getSignalIcon(row.potencia)}</td>
                <td>${row.serie}</td>
                <td>${row.gpon}</td>
                <td>${row.zona}</td>
                <td><a href="${row.ver}">Ver</a></td>
            </tr>`;
          });
  
      // Inserta las filas en el cuerpo de la tabla
      tableBody.innerHTML = rows.join('');
  
      dataTable = $("#olt-table").DataTable({
        responsive: true,
        pageLength: 10,
        lengthMenu: false,
        deferRender: false,
        searching:false,
        language: {
            search: "Buscar",
            info: "Mostrando _START_ a _END_ de _TOTAL_ resultados",
            lengthMenu: ""
        }
      });
    })
      .catch(error => console.error("Error:", error));
    
    });
});
