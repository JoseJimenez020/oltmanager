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
  let refreshTimer = null;

  async function refreshInfo() {
    showLoadingInfo();

    const pass = new URLSearchParams(location.search).get('id');
    const response = await fetch(`../controllers/refresh_onu.php?pass=${encodeURIComponent(pass)}`);

    let data;
    try {
      data = await response.json();
    } catch (e) {
      statusPlaceholder.innerText = 'Error servidor';
      infoPlaceholder.innerText = 'Error servidor';
      return;
    }

    if (!response.ok) {
      statusPlaceholder.innerText = data.error || 'ONU no disponible';
      infoPlaceholder.innerText = '-';
      if (refreshTimer) clearInterval(refreshTimer); // deja de insistir
      return;
    }

    statusPlaceholder.innerHTML = data.status || '—';
    infoPlaceholder.innerHTML = data.distanciaPotencia || '—';
  }

  refreshInfo();
  setTimeout(function () {
    refreshTimer = setInterval(refreshInfo, 10 * 1000);
  }, 10 * 1000);
});