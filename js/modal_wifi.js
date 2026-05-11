document.addEventListener("DOMContentLoaded", function () {
    const wifiCheckbox = document.querySelector(".wifi-toggle");
    const modal = document.getElementById("modalToggleWifi");
    const cancelBtn = document.getElementById("noToggleWifiBtn");

    // Mostrar el modal cuando se marca el checkbox
    wifiCheckbox.addEventListener("change", function () {
        if (this.checked) {
            modal.style.display = "flex"; // Muestra el modal cuando se marca el checkbox
        } else {
            modal.style.display = "none"; // Oculta el modal cuando se desmarca el checkbox
        }
    });

    // Cerrar el modal al hacer clic en el botón "Cancelar"
    cancelBtn.addEventListener("click", function () {
        modal.style.display = "none"; // Oculta el modal
        wifiCheckbox.checked = false; // Desmarca el checkbox al cancelar
    });

    // Cerrar el modal si el usuario hace clic fuera del contenido
    window.addEventListener("click", function (event) {
        if (event.target === modal) {
            modal.style.display = "none"; // Oculta el modal
            wifiCheckbox.checked = false; // Desmarca el checkbox
        }
    });
});