// Función para mostrar el modal
function mostrarModal(vlans) {
  document.getElementById("modal_vlan").style.display = "block"; // Muestra el modal
  // Aquí puedes añadir más lógica si es necesario, como agregar vlans
}

// Función para cerrar el modal
function cerrarModal() {
  document.getElementById("modal_vlan").style.display = "none"; // Oculta el modal
}

// Función para abrir el dropdown de VLAN
const selectBox = document.querySelector(".select-box");
const dropdown = document.querySelector(".dropdown");
const span = selectBox.querySelector("span:first-child");
const botonAttachedVlan = document.getElementById("botonAttachedVlan");

selectBox.addEventListener("click", () => {
  dropdown.classList.toggle("open"); // Abre o cierra el dropdown al hacer clic
});
let selectedVLANs = [];
botonAttachedVlan.addEventListener("click", function () {
  const dropdown = document.getElementById("vlan-dropdown");
  const span = document.getElementById("vlans");
  
  dropdown.innerHTML = "";
  selectedVLANs = [];

  const searchParams = new URLSearchParams(window.location.search);
  const id = searchParams.get('id');
  fetch(`../api/vlanProfile.php?accion=formDhcp&id=${id}`)  // Reemplaza con tu URL real
      .then(response => response.json())
      .then(data => {
          const todasVlans = data.vlans;
          const vlansAsociadas = data.vlan.map(v => v.IdOltVlan); // extraemos los ID ya asociados

          todasVlans.forEach((vlan, index) => {
              const checked = vlansAsociadas.includes(vlan.VlanId) ? 'checked' : '';
              const value = vlan.Vlan;
              const labelText = vlan.Vlan + (vlan.VlanDescription ? ` - ${vlan.VlanDescription}` : "");

              const li = document.createElement("li");

              const input = document.createElement("input");
              input.type = "checkbox";
              input.name = "vlan[]";
              input.value = value;
              input.id = `check_vlan_${index}`;
              if (checked) {
                  input.checked = true;
                  selectedVLANs.push(value); // Preseleccionamos el valor
              }
              
              const label = document.createElement("label");
              label.htmlFor = input.id;
              label.textContent = labelText;

              input.addEventListener("change", () => {
                  if (input.checked) {
                      selectedVLANs.push(value);
                  } else {
                      selectedVLANs = selectedVLANs.filter(v => v !== value);
                  }
                  span.textContent = selectedVLANs.length > 0 ? selectedVLANs.join(" ") : "Seleccionar VLAN";
              });

              li.appendChild(input);
              li.appendChild(label);
              dropdown.appendChild(li);
          });

          // Mostrar VLANs ya seleccionadas al cargar
          span.textContent = selectedVLANs.length > 0 ? selectedVLANs.join(" ") : "Seleccionar VLAN";
      })
      .catch(error => console.error("Error cargando VLANs:", error));
});
// Evento para el botón "Update"
document.getElementById("vlan_update").addEventListener("click", async function(event){
  event.preventDefault();
  const form = document.getElementById("vlan_form");

  // Crear un objeto FormData con los datos del formulario
  const formData = new FormData(form);
  const redata = {};
  
  formData.forEach((value, key) => {
    redata[key] = value;
  });
  redata.vlans = selectedVLANs;
  // Verificamos si se han seleccionado VLANs
  if (formData.has('vlan[]')) {
    Swal.fire({
      title: 'Cambiando configuracion',
      text: 'Por favor espera un momento',
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => {
        Swal.showLoading();
      },
      background: '#1a1a1a',
      color: '#fff',
      customClass: {
        popup: 'my-loading-popup',
        title: 'my-loading-title',
      },
    });
    try {
      const response = await fetch("../api/onuProfile.php", {
      method: "PUT",
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(redata)
    });
    if (!response.ok) {
      throw new Error(`Response status: ${response.status}`);
    }

    const json = await response.json();
    if (json.status) {
      location.reload();
    }
    } catch (error) {
      console.error("Error al enviar los datos:", error);
        alert("Ocurrió un error al enviar los datos.");
    }
  } else {
    alert("Por favor, selecciona al menos una VLAN.");
  }
});

// Función para cerrar el modal
function cerrarModal_mode() {
  document.getElementById("modal_onu_mode").style.display = "none";
}

// Función para mostrar el modal
function mostrarModal_onu_mode() {
  document.getElementById("modal_onu_mode").style.display = "block";
}

// Cerrar el modal al hacer clic fuera de él
window.addEventListener("click", (event) => {
  const modal = document.getElementById("modal_onu_mode");
  if (event.target === modal) {
    cerrarModal_mode();
  }
});
