document.addEventListener("DOMContentLoaded", function () {
  const searchParams = new URLSearchParams(window.location.search);
  let id = searchParams.get('id');
  async function onuProfile(id) {
    try {
      const response = await fetch(`../api/onuProfile.php?id=${id}&accion=onuProfile`);
      const data = await response.json();
      if (!data.status) {
        console.error("Error desde el servidor");
        return;
      }
      const info = data.onu;
      // Asignar valores a los elementos por ID
      document.getElementById("onuNombre").textContent = info.OntNombre || "-";
      document.getElementById("onuOltNombre").textContent = info.OltName || "-";
      document.getElementById("onuTarjeta").textContent = info.IndexCard || "-";
      document.getElementById("onuPuerto").textContent = info.IndexPort || "-";
      document.getElementById("onuInterface").textContent = info.OnuInterface || "-";
      document.getElementById("onuType").textContent = info.OntModelo || "-";
      document.getElementById("onuZona").textContent = info.Zona || "-";
      document.getElementById("onuSn").textContent = info.OnuSn || "-";
      // VLAN link
      document.getElementById("botonAttachedVlan").textContent = data.vlan;
      document.getElementById("onuSpeedDown").textContent = info.Down || "-";
      document.getElementById("onuSpeedUp").textContent = info.Up || "-";
    } catch (error) {
      console.error("Error al cargar los datos de la ONU:", error);
    }
  }
  onuProfile(id);
  // Capturamos el botón por su id
  const boton = document.getElementById("mostrarDatosBtn");
  const botonConfig = document.getElementById("running-config");
  const botonIpMac = document.getElementById('mostrarIpMac');
  const resultadoDiv = document.getElementById("resultado");

  const botonReinicar = document.getElementById("reiniciar");
  const botonRestore = document.getElementById("botonRestore");
  const botonResync = document.getElementById("botonResync");
  const botonEliminar = document.getElementById("botonEliminar");
  const formGestion = document.getElementById("gestionOnu");
  const botonGestion = document.getElementById("botonGestion");
  const formSpeed = document.getElementById("speedProfile");
  const botonSpeed = document.getElementById("openPopup");
  const wifi = document.querySelectorAll('.container_delete_option button');
  const submitOnuMode = document.getElementById("onu_mode_form");
  const closeGp = document.getElementById("gestionPopup");
  const popup = document.getElementById("loginPopup");
  let resultadosVisibles = false;

  boton.addEventListener("click", async function () {
    if (resultadosVisibles) {
      // Ocultar resultados
      resultadoDiv.style.display = 'none';
      boton.textContent = 'Obtener estado';
      resultadosVisibles = false;
    } else {
      // Mostrar y cargar resultados
      resultadoDiv.style.display = 'block';
      boton.textContent = 'Ocultar estado';  // ¡Este es el cambio importante!
      resultadosVisibles = true;

      const id = boton.getAttribute("data-id");
      const dataAccion = boton.getAttribute("data-accion");

      // Mostrar loader
      resultadoDiv.innerHTML = '<p>Cargando información...</p>';
      try {
        const response = await fetch(`../api/onuProfile.php?id=${id}&accion=${dataAccion}`, {
          method: "GET",
        });
        if (!response.ok) {
          throw new Error(`Response status: ${response.status}`);
        }
        const data = await response.json();
        resultadoDiv.innerHTML = "<h2>Información de Estado</h2>";
        const lista = document.createElement("ul");

        for (const clave in data.onu) {
          if (data.onu.hasOwnProperty(clave)) {
            const listItem = document.createElement("li");
            listItem.textContent = `${clave}: ${data.onu[clave]}`;
            lista.appendChild(listItem);
          }
        }

        resultadoDiv.appendChild(lista);
      } catch (error) {
        console.error("Error:", error);
        resultadoDiv.innerHTML = '<p class="error">Error al cargar datos</p>';
        // Si hay error, permitir volver a intentar
        boton.textContent = 'Obtener estado';
        resultadosVisibles = false;
      }
    }
  });

  botonConfig.addEventListener("click", async function () {
    const id = botonConfig.getAttribute("data-id");
    const dataAccion = botonConfig.getAttribute("data-accion");

    resultadoDiv.innerHTML = '<p>Cargando configuración...</p>';
    resultadoDiv.style.display = 'block';
    try {
      const response = await fetch(`../api/onuProfile.php?id=${id}&accion=${dataAccion}`, {
        method: "GET"
      });
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const data = await response.json();
      resultadoDiv.innerHTML = "<h2>Configuración Actual</h2>";
      const lista = document.createElement("ul");
      for (const clave in data) {
        if (data.hasOwnProperty(clave)) {
          const listItem = document.createElement("li");
          listItem.textContent = `${clave}: ${data[clave]}`;
          lista.appendChild(listItem);
        }
      }
      resultadoDiv.appendChild(lista);
    } catch (error) {
      console.error("Error al enviar los datos:", error);
    }
  });
  botonIpMac.addEventListener("click", async function () {
    const id = botonIpMac.getAttribute("data-id");
    const dataAccion = botonIpMac.getAttribute("data-accion");

    resultadoDiv.innerHTML = '<p>Cargando configuración...</p>';
    resultadoDiv.style.display = 'block';
    try {
      const response = await fetch(`../api/onuProfile.php?id=${id}&accion=${dataAccion}`, {
        method: "GET"
      });
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const data = await response.json();
      resultadoDiv.innerHTML = "<h2>Configuración Actual</h2>";
      const lista = document.createElement("ul");
      for (const clave in data.ip) {
        if (data.ip.hasOwnProperty(clave)) {
          const listItem = document.createElement("li");
          listItem.textContent = `Ip: ${data.ip[clave]}`;
          lista.appendChild(listItem);
        }
      }
      resultadoDiv.appendChild(lista);
    } catch (error) {
      console.error("Error al enviar los datos:", error);
    }
  });
  botonReinicar.addEventListener("click", async function (event) {
    event.preventDefault(); // Prevenir la acción de navegación
    const metodo = botonReinicar.getAttribute("data-metodo");
    const id = botonReinicar.getAttribute("data-id");
    // Crear el objeto que será enviado al archivo PHP
    const data = {
      accion: metodo,
      id: id
    };

    Swal.fire({
      title: 'Reiniciando ONU',
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
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
      });
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const json = await response.json();
      if (json.status) {
        location.reload();
      }
    } catch (error) {
      console.error("Error:", error);
    }
  });

  document.addEventListener("click", async function (event) {

    const boton = event.target.closest("#botonDisable");
    if (boton) {
      event.preventDefault();

      const metodo = boton.getAttribute("data-metodo");
      const id = boton.getAttribute("data-id");
      const accion = boton.getAttribute("data-accion");

      const data = {
        metodo: metodo,
        id: id,
        accion: accion
      };

      Swal.fire({
        title: 'Gestionando ONU',
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
            'Content-Type': 'application/json'
          },
          body: JSON.stringify(data) // Enviar los datos en formato JSON
        });
        if (!response.ok) {
          throw new Error(`Response status: ${response.status}`);
        }

        const json = await response.json();
        if (json.status) {
          location.reload();
        }
      } catch (error) {
        console.error("Error:", error);
      }
    }
  });
  botonRestore.addEventListener("click", async function (event) {
    event.preventDefault(); // Prevenir la acción de navegación
    const metodo = botonRestore.getAttribute("data-metodo");
    const id = botonRestore.getAttribute("data-id");

    const data = {
      accion: metodo,
      id: id
    };


    Swal.fire({
      title: 'Restableciendo de fabrica',
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
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(data) // Enviar los datos en formato JSON
      });
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const json = await response.json();
      if (json.status) {
        location.reload();
      }
    } catch (error) {
      console.error("Error:", error);
    }
  });

  botonResync.addEventListener("click", async function (event) {
    event.preventDefault(); // Prevenir la acción de navegación
    const id = botonResync.getAttribute("data-id");
    const metodo = botonResync.getAttribute("data-metodo");

    // Crear el objeto que será enviado al archivo PHP
    const data = {
      accion: metodo,
      id: id,
    };


    Swal.fire({
      title: 'Resincronizando ONU',
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
          "Content-Type": "application/json",
        },
        body: JSON.stringify(data), //JSON.stringify(data) // Enviar los datos en formato JSON
      });
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const json = await response.json();
      if (json.status) {
        location.reload();
      }
    } catch (error) {

    }
  });
  botonEliminar.addEventListener("click", async function (event) {
    event.preventDefault(); // Prevenir la acción de navegación

    const id = botonEliminar.getAttribute("data-id");
    const metodo = botonEliminar.getAttribute("data-metodo");


    // Crear el objeto que será enviado al archivo PHP
    const data = {
      accion: metodo,
      id: id,
    };

    // Alerta de confirmación
    const confirmacion = await Swal.fire({
      title: 'Eliminar ONU',
      text: "¿Deseas eliminar la ONU?",
      icon: 'warning',
      showCancelButton: true,
      confirmButtonColor: '#d33',
      cancelButtonColor: '#3085d6',
      confirmButtonText: 'Sí',
      cancelButtonText: 'Cancelar',
      background: '#1a1a1a',
      color: '#fff',
    });

    // Si el usuario cancela, no hacemos nada
    if (!confirmacion.isConfirmed) return;

    // Mostrar alerta de cargando si confirmó
    Swal.fire({
      title: 'Eliminando ONU',
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

    console.log("Metodo:", data);

    try {
      const response = await fetch(`../api/onuProfile.php?id=${id}&accion=${'onu'}`, {
        method: "DELETE",
        headers: {
          "Content-Type": "application/x-www-form-urlencoded",
        },
        body: new URLSearchParams(data),
      });
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const json = await response.json();
      if (json.status) {
        Swal.fire({
          title: '¡Eliminada!',
          text: 'La ONU fue eliminada correctamente.',
          icon: 'success',
          confirmButtonColor: '#28a745',
          background: '#1a1a1a',
          color: '#fff',
        }).then(() => {
          window.location.href = "../views";
        });
      } else {
        Swal.fire({
          title: 'Error',
          text: 'No se pudo eliminar la ONU.',
          icon: 'error',
          confirmButtonColor: '#d33',
        });
      }
    } catch (error) {
      Swal.fire({
        title: 'Error inesperado',
        text: error,
        icon: 'error',
        confirmButtonColor: '#d33',
      });
    }
  });

  formGestion.addEventListener("submit", async function (event) {
    event.preventDefault(); // Evitar que se envíe el formulario de la forma tradicional

    // Obtener los datos del formulario
    const formData = new FormData(formGestion);
    const redata = {};
    formData.forEach((value, key) => {
      redata[key] = value;
    });

    Swal.fire({
      title: 'Gestionando ONU',
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
      // Realizar la solicitud fetch y esperar la respuesta
      const response = await fetch("../api/onuProfile.php", {
        method: "PUT",
        headers: {
          'Content-Type': 'application/json',
        },
        body: JSON.stringify(redata), // Los datos del formulario van en el body de la solicitud
      });

      // Verificamos si la respuesta es exitosa
      if (!response.ok) {
        console.log("Error");
        throw new Error("Error en la solicitud");
      }

      // Convertimos la respuesta en formato JSON
      const data = await response.json();


      // Ejecutamos el código solo si la respuesta es exitosa
      if (data.status) {
        closeGp.classList.remove("popup");
        closeGp.classList.add("hidden");


        location.reload();
      }
    } catch (error) {
      // Si ocurre un error, lo capturamos y mostramos un mensaje
      console.error(error);
    }
  });
  botonGestion.addEventListener("click", async function () {
    try {
      const searchParams = new URLSearchParams(window.location.search);
      const id = searchParams.get('id');
      const response = await fetch(`../api/vlanProfile.php?accion=formDhcp&id=${id}`);
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const data = await response.json();
      if (data.status && Array.isArray(data.vlan)) {
        const select = document.querySelector("#gestionOnu #vlansSelectForm");
        select.innerHTML = "";
        data.vlans.forEach(item => {
          const option = document.createElement("option");
          option.value = item.Vlan;
          const desc = item.VlanDescription ? item.VlanDescription : "";
          option.textContent = `${item.Vlan} ${desc}`;
          if (item.VlanId === data.vlan[0]['IdOltVlan']) {
            option.selected = true;
          }
          select.appendChild(option);
        });
      } else {
        console.error("Respuesta inesperada del servidor:", data);
      }
    } catch (error) {
      console.error("Error al cargar los datos:", error);
    }
  });

  formSpeed.addEventListener("submit", async function (event) {
    event.preventDefault(); // Evitar que se envíe el formulario de la forma tradicional

    // Obtener los datos del formulario
    const formData = new FormData(formSpeed);
    const data = {};
    formData.forEach((value, key) => {
      data[key] = value;
    });
    Swal.fire({
      title: 'Cambiando velocidad ONU',
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
        body: JSON.stringify(data), // Los datos del formulario van en el body de la solicitud
      });
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const json = await response.json();
      if (json.status) {
        popup.classList.remove("popup");
        popup.classList.add("hidden");
        location.reload();
      }
    } catch (error) {
      console.error("Error:", error);
      responseDiv.innerHTML = "Hubo un error al enviar los datos.";
    }
  });
  botonSpeed.addEventListener("click", async function () {
    const searchParams = new URLSearchParams(window.location.search);
    const id = searchParams.get('id');
    try {
      const response = await fetch(`../api/speedProfile.php?accion=speedFormOnu&speed=${id}`);
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const data = await response.json();
      if (data.status && Array.isArray(data.downs)) {
        const selectDown = document.querySelector("#speedProfile #selectSpeedDown");
        selectDown.innerHTML = "";
        data.downs.forEach(item => {
          const option = document.createElement("option");
          option.value = item.IdProfile;
          option.textContent = item.ProfileName;
          if (item.IdProfile === data.down) {
            option.selected = true;
          }
          selectDown.appendChild(option);
        });
        const selectUp = document.querySelector("#speedProfile #selectSpeedUp");
        selectUp.innerHTML = "";
        data.ups.forEach(item => {
          const option = document.createElement("option");
          option.value = item.IdProfile;
          option.textContent = item.ProfileName;
          if (item.IdProfile === data.up) {
            option.selected = true;
          }
          selectUp.appendChild(option);
        });
      } else {
        console.error("Respuesta inesperada del servidor:", data);
      }
    } catch (error) {
      console.error("Error al cargar los datos:", error);
    }
  });
  wifi.forEach(wifi => {
    if (wifi.id != 'noToggleWifiBtn') {
      wifi.addEventListener('click', async function () {
        const wifi = this.getAttribute("data-wifi");
        const id = this.getAttribute("data-id");
        const accion = this.getAttribute("data-accion");

        const redata = {
          wifi: wifi,
          id: id,
          accion: accion
        };
        Swal.fire({
          title: 'Cambian configuracion',
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
              "Content-Type": "Content-Type': 'application/json",
            },
            body: JSON.stringify(redata), // Convertimos los datos a formato URL codificado
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
        }
      });
    }
  });
  submitOnuMode.addEventListener("submit", async function (e) {
    e.preventDefault();
    const formData = new FormData(submitOnuMode);
    const data = {};
    formData.forEach((value, key) => {
      data[key] = value;
    });
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
        body: JSON.stringify(data),
      });
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }

      const json = await response.json();
      if (json.status) {
        document.getElementById("modal_onu_mode").style.display = "none";
        location.reload();
      }
    } catch (error) {
      console.error("Error:", error);
      responseDiv.innerHTML = "Hubo un error al enviar los datos.";
    }
  });
});

