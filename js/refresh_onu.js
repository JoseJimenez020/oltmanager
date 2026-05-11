document.addEventListener('DOMContentLoaded', function () {
    // Selección de elementos del DOM
    const statusPlaceholder = document.querySelector(".status");
    const infoPlaceholder = document.querySelector(".distancia-potencia");
    const botonDisableFromOnu = document.querySelector("#disableOnuFrom");
    const mgmtIpFromOnu = document.querySelector("#mgmtIpFrom");

    // Función para mostrar el indicador de carga
    function showLoadingInfo() {
        const loadingSpinner = '<span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">autorenew</span>';
        statusPlaceholder.innerHTML = loadingSpinner;
        infoPlaceholder.innerHTML = loadingSpinner;

    }

    // Función para cargar los datos
    async function refreshInfo() {
        showLoadingInfo();
      
        const pass     = new URLSearchParams(location.search).get('id');
        const response = await fetch(`../controllers/refresh_onu.php?pass=${encodeURIComponent(pass)}`);
      
        let data;
        try {
          data = await response.json();
        } catch (e) {
          console.error('Invalid JSON from server', e);
          statusPlaceholder.innerText = 'Error servidor';
          infoPlaceholder.innerText  = 'Error servidor';
          return;
        }
      
        if (!response.ok) {
          console.error('Server returned error:', data);
          statusPlaceholder.innerText = 'Error servidor';
          infoPlaceholder.innerText  = 'Error servidor';
          return;
        }
      
        // Finally: render your Estado + ONU/OLT Rx Señal
        statusPlaceholder.innerHTML            = data.status || '—';
        infoPlaceholder.innerHTML              = data.distanciaPotencia || '—';
      
        // handle admin button and IP as before…
      }
    refreshInfo();
    // Ejecutar la función 5 minutos después de cargar la página
    setTimeout(function () {

        // Repetir cada 5 minutos
        setInterval(refreshInfo, 10 * 1000); // 5 minutos en milisegundos
    }, 10 * 1000); // 5 minutos en milisegundos
});