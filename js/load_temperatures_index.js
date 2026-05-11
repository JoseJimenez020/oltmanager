document.addEventListener('DOMContentLoaded', function () {
    const temperatureContainer = document.getElementById('olt-temp');

    // Realiza la solicitud a `load_temperatures.php`
    fetch('controllers/load_temperatures.php')
        .then(response => response.json())
        .then(data => {
            // Limpia el contenido actual
            temperatureContainer.innerHTML = '';

            // Agrega cada temperatura al contenedor
            data.forEach(item => {
                const temperatureItem = `
                <div class="item online">
                    <div class="icon">
                        <span class="material-symbols-outlined">device_thermostat</span>
                    </div>
                    <div class="right">
                        <div class="info">
                        
                            <h3>${item.nameOLT}</h3>
                            <small class="text-muted">${item.time}</small>
                        
                        </div>
                        <h5 class="succes">${item.temp}°</h5>
                        <h3>${item.dias} días</h3>
                    </div>
                </div>`;
                temperatureContainer.insertAdjacentHTML('beforeend', temperatureItem);
            });
        })
        .catch(error => {
            console.error('Error al cargar temperaturas:', error);
            temperatureContainer.innerHTML = '<p>Error al cargar las temperaturas.</p>';
        });
});