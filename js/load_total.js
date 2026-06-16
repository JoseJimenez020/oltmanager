document.addEventListener('DOMContentLoaded', function () {
    const h1Offline = document.querySelector(".total-offline");
    const h1LowSignal = document.querySelector(".total-low");
    const h1Ok = document.querySelector(".total-ok");
    const h1unconf = document.querySelector(".total-unconf");
    const dOnline = document.getElementById("online");
    const dOffline = document.getElementById("offline");
    const dLow = document.getElementById("low");

    function showLoadingH1() {
        const loadingSpinner = '<span class="material-symbols-outlined" style="animation: spin 1s linear infinite;">autorenew</span>';
        h1unconf.innerHTML = loadingSpinner;
        h1Offline.innerHTML = loadingSpinner;
        h1LowSignal.innerHTML = loadingSpinner;
        h1Ok.innerHTML = loadingSpinner;
        dOnline.innerHTML = loadingSpinner;
        dOffline.innerHTML = loadingSpinner;
        dLow.innerHTML = loadingSpinner;
    }

    function loadTotal() {
        showLoadingH1();

        fetch('../controllers/total_class_onus.php')
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                data.forEach(row => {
                    h1Ok.innerHTML      = row.totalOnline;
                    h1Offline.innerHTML = row.totalOff;
                    h1LowSignal.innerHTML = row.totalLow;
                    dOnline.innerHTML   = row.descripcionOk;
                    dOffline.innerHTML  = row.descripcionOffline;
                    dLow.innerHTML      = row.descripcionLow;
                });
            })
            .catch(error => {
                console.error('Error al cargar totales:', error);
                h1Ok.innerHTML = 'Error';
            });

        fetch('../controllers/load_table_unconf.php')
            .then(response => {
                if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
                return response.json();
            })
            .then(data => {
                h1unconf.innerHTML = data.length + ' ONUS';
            })
            .catch(error => {
                console.error('Error al cargar desautorizadas:', error);
                h1unconf.innerHTML = 'Error';
            });
    }

    loadTotal();
});
