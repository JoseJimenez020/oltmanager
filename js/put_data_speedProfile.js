document.addEventListener("DOMContentLoaded", function () {
    // Obtener el formulario y el div de respuesta
    const form = document.getElementById('speedProfile');
    const responseDiv = document.getElementById('resultado');
    const popup = document.getElementById('loginPopup');
    // Escuchar el evento de envío del formulario
    form.addEventListener('submit', function (event) {
        event.preventDefault(); // Evitar que se envíe el formulario de la forma tradicional

        // Obtener los datos del formulario
        const formData = new FormData(form);

        // Enviar los datos con fetch
        fetch('../controllers/put_data_speedProfile.php', {
            method: 'POST',
            body: formData // Los datos del formulario van en el body de la solicitud
        })
        .then(response => response.json())  // Esperamos que el servidor devuelva un JSON
        .then(data => {
            // Mostrar la respuesta en el div
            //responseDiv.innerHTML = `Respuesta del servidor: ${data.message}`;
            // Mostrar los datos obtenidos en el div
            //const resultadoDiv = document.getElementById("resultado");
            //resultadoDiv.innerHTML = '<h2>Datos obtenidos:</h2>';

            // Crear una lista para mostrar la información
            //const lista = document.createElement('ul');
            //for (const clave in data) {
            //    if (data.hasOwnProperty(clave)) {
            //        const listItem = document.createElement('li');
            //        listItem.textContent = `${data[clave]}`;
            //        lista.appendChild(listItem);
            //    }
            //}
            //resultadoDiv.appendChild(lista);
            popup.classList.remove('popup');
            popup.classList.add('hidden');
        })
        .catch(error => {
            console.error('Error:', error);
            responseDiv.innerHTML = "Hubo un error al enviar los datos.";
        });
    });
});