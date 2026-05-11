document.addEventListener('DOMContentLoaded', async function () {
    const temperatureContainer = document.getElementById('olt-temp');
    try {
        // Realizar la solicitud fetch y esperar la respuesta
        const response = await fetch("../controllers/load_temperatures.php", {
          method: "GET"
        });
  
        // Verificamos si la respuesta es exitosa
        if (!response.ok) {
          console.log("Error");
          throw new Error("Error en la solicitud");
        }
  
        // Convertimos la respuesta en formato JSON
        const data = await response.json();
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
            
      } catch (error) {
        console.error('Error al cargar temperaturas:', error);
        temperatureContainer.innerHTML = '<p>Error al cargar las temperaturas.</p>';
      }
});