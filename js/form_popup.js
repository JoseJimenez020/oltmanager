$(".botonF").hover(function () {
  $(".btn").addClass("animacionVer");
});
$(".contenedor").mouseleave(function () {
  $(".btn").removeClass("animacionVer");
});

const openBtn = document.getElementById("openPopup");
const openBotonGestion = document.getElementById("botonGestion");

const popup = document.getElementById("loginPopup");
const formGestion = document.getElementById("gestionPopup");

const closeLoginBtn = document.querySelector("#loginPopup .close-btn");
const closeGestionBtn = document.querySelector("#gestionPopup .close-btn");

// Abrir popup de Speed Profile
openBtn.addEventListener("click", () => {
  popup.classList.add("popup");
  popup.classList.remove("hidden");
});

// Abrir popup de Gestión de ONU
openBotonGestion.addEventListener("click", () => {
  formGestion.classList.add("popup");
  formGestion.classList.remove("hidden");
});

// Cerrar popup de Speed Profile
closeLoginBtn.addEventListener("click", () => {
  popup.classList.remove("popup");
  popup.classList.add("hidden");
});

// Cerrar popup de Gestión de ONU
closeGestionBtn.addEventListener("click", () => {
  formGestion.classList.remove("popup");
  formGestion.classList.add("hidden");
});

// Cerrar popups al hacer clic fuera de ellos
window.addEventListener("click", (e) => {
  if (e.target === popup) {
    popup.classList.add("hidden");
  }
  if (e.target === formGestion) {
    formGestion.classList.add("hidden");
  }
});
