document.addEventListener('DOMContentLoaded', function () {
    // Obtener el modal y el botón de cierre
    const modal = document.getElementById('modalConfigureUplink');
    const closeModalButton = document.getElementById('closeConfigureModal');

    // Delegación de eventos para los botones de configuración
    document.querySelector('.container').addEventListener('click', function (event) {
        const button = event.target.closest('.configure-btn-uplink');
        if (button) {
            // Aquí se puede personalizar el nombre del puerto según sea necesario
            const uplinkPortName = "Port Name"; // Reemplázalo con el nombre real del puerto si lo tienes
            document.getElementById('uplinkPortName').textContent = uplinkPortName;

            // Mostrar el modal
            modal.style.display = 'flex';
        }
    });

    // Cerrar el modal cuando se haga clic en el botón de cierre
    closeModalButton.addEventListener('click', function () {
        modal.style.display = 'none';
    });

    // Cerrar el modal si se hace clic fuera de él
    window.addEventListener('click', function (event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    });
});