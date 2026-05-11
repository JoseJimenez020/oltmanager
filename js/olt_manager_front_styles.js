$(document).ready(function () {
  // Inicializa DataTables
  $("#oltsgeneralTable").DataTable();

  // Variables del modal
  var modal = $("#modalGeneral"); // Modal de desactivación
  var modalName = $("#modalName"); // Nombre a mostrar en el modal

  // Asegurarse de que el modal esté oculto al cargar la página
  modal.hide(); // Ocultar el modal por defecto al cargar la página

  // Abrir el modal al hacer clic en "Ver"
  $(".open-modal").on("click", function () {
    var name = $(this).data("name"); // Obtiene el nombre del botón
    modalName.text(name); // Mostrar el nombre en el modal
    modal.fadeIn(); // Mostrar el modal con fadeIn
  });

  // Cerrar el modal al hacer clic en el botón No o fuera del modal
  $("#noBtn, .close, #modalGeneral").on("click", function (event) {
    if (
      event.target === modal[0] ||
      $(event.target).hasClass("no-btn") ||
      $(event.target).hasClass("close")
    ) {
      modal.fadeOut(); // Ocultar el modal con fadeOut
    }
  });

  // Acción de botón Sí
  $("#yesBtn").on("click", function () {
    alert("Usuario desactivado: " + modalName.text()); // Aquí va la acción al hacer clic en Sí
    modal.fadeOut(); // Cierra el modal con fadeOut
  });
});

$(document).ready(function () {
  // Variables del modal de eliminación
  var modalDelete = $("#modalDelete"); // Modal de eliminación
  var modalDeleteName = $("#modalDeleteName"); // Nombre a mostrar en el modal

  // Asegurarse de que el modal de eliminación esté oculto al cargar la página
  modalDelete.hide(); // Ocultar el modal de eliminación por defecto al cargar la página

  // Abrir el modal de eliminación al hacer clic en "Delete"
  $(".delete").on("click", function () {
    var name = $(this).data("name"); // Obtiene el nombre del usuario desde el atributo data-name
    modalDeleteName.text(name); // Mostrar el nombre en el modal
    modalDelete.fadeIn(); // Mostrar el modal con fadeIn
  });

  // Cerrar el modal de eliminación al hacer clic en el botón No o fuera del modal
  $("#noDeleteBtn, .close, #modalDelete").on("click", function (event) {
    if (
      event.target === modalDelete[0] ||
      $(event.target).hasClass("no-btn") ||
      $(event.target).hasClass("close")
    ) {
      modalDelete.fadeOut(); // Ocultar el modal con fadeOut
    }
  });

  // Acción de botón Sí
  $("#yesDeleteBtn").on("click", function () {
    var name = modalDeleteName.text();
    alert("Usuario eliminado: " + name); // Acción de eliminación
    modalDelete.fadeOut(); // Cierra el modal con fadeOut
  });
});

$(document).ready(function () {
  // Variables del modal de eliminación de ODB
  var modalDeleteODB = $("#modalDeleteODB"); // Modal de eliminación de ODB
  var modalDeleteODBName = $("#modalDeleteODBName"); // Nombre de la ODB en el modal

  // Asegurarse de que el modal de eliminación esté oculto al cargar la página
  modalDeleteODB.hide(); // Ocultar el modal de eliminación por defecto al cargar la página

  // Abrir el modal de eliminación al hacer clic en "Delete"
  $(".delete").on("click", function () {
    var name = $(this).closest("tr").find("td:first").text(); // Obtiene el nombre del ODB (columna 1)
    modalDeleteODBName.text(name); // Mostrar el nombre de la ODB en el modal
    modalDeleteODB.fadeIn(); // Mostrar el modal con fadeIn
  });

  // Cerrar el modal al hacer clic en el botón No o fuera del modal
  $("#noDeleteODBBtn, .close, #modalDeleteODB").on("click", function (event) {
    if (
      event.target === modalDeleteODB[0] ||
      $(event.target).hasClass("no-btn") ||
      $(event.target).hasClass("close")
    ) {
      modalDeleteODB.fadeOut(); // Ocultar el modal con fadeOut
    }
  });

  // Acción de botón Sí (Eliminar ODB)
  $("#yesDeleteODBBtn").on("click", function () {
    var name = modalDeleteODBName.text();
    alert("ODB eliminado: " + name); // Acción de eliminación del ODB
    modalDeleteODB.fadeOut(); // Cierra el modal con fadeOut
  });
});

$(document).ready(function () {
  // Variables del modal de desactivar OLT
  var modalDisableOLT = $("#modalDisableOLT"); // Modal de desactivación de OLT
  var modalDisableOLTName = $("#modalDisableOLTName"); // Nombre del OLT en el modal

  // Variables del modal de eliminar OLT
  var modalDeleteOLT = $("#modalDeleteOLT"); // Modal de eliminación de OLT
  var modalDeleteOLTName = $("#modalDeleteOLTName"); // Nombre del OLT en el modal

  // Asegurarse de que los modales estén ocultos al cargar la página
  modalDisableOLT.hide(); // Ocultar el modal de desactivación
  modalDeleteOLT.hide(); // Ocultar el modal de eliminación

  // Abrir el modal de desactivación al hacer clic en "Disable"
  $(".action_olts_disable").on("click", function () {
    var name = $(this).closest("tr").find("td:nth-child(3)").text(); // Obtiene el nombre del OLT (columna 3)
    modalDisableOLTName.text(name); // Mostrar el nombre del OLT en el modal
    modalDisableOLT.fadeIn(); // Mostrar el modal con fadeIn
  });

  // Abrir el modal de eliminación al hacer clic en "Delete"
  $(".action_olts_delete").on("click", function () {
    var name = $(this).closest("tr").find("td:nth-child(3)").text(); // Obtiene el nombre del OLT (columna 3)
    modalDeleteOLTName.text(name); // Mostrar el nombre del OLT en el modal
    modalDeleteOLT.fadeIn(); // Mostrar el modal con fadeIn
  });

  // Cerrar el modal de desactivación al hacer clic en el botón No o fuera del modal
  $("#noDisableOLTBtn, .close, #modalDisableOLT").on("click", function (event) {
    if (
      event.target === modalDisableOLT[0] ||
      $(event.target).hasClass("no-btn") ||
      $(event.target).hasClass("close")
    ) {
      modalDisableOLT.fadeOut(); // Ocultar el modal con fadeOut
    }
  });

  // Cerrar el modal de eliminación al hacer clic en el botón No o fuera del modal
  $("#noDeleteOLTBtn, .close, #modalDeleteOLT").on("click", function (event) {
    if (
      event.target === modalDeleteOLT[0] ||
      $(event.target).hasClass("no-btn") ||
      $(event.target).hasClass("close")
    ) {
      modalDeleteOLT.fadeOut(); // Ocultar el modal con fadeOut
    }
  });

  // Acción de botón Sí para desactivar OLT
  $("#yesDisableOLTBtn").on("click", function () {
    var name = modalDisableOLTName.text();
    alert("OLT desactivado: " + name); // Acción de desactivación del OLT
    modalDisableOLT.fadeOut(); // Cierra el modal con fadeOut
  });

  // Acción de botón Sí para eliminar OLT
  $("#yesDeleteOLTBtn").on("click", function () {
    var name = modalDeleteOLTName.text();
    alert("OLT eliminado: " + name); // Acción de eliminación del OLT
    modalDeleteOLT.fadeOut(); // Cierra el modal con fadeOut
  });
});

$(document).ready(function () {
  var formModal = $("#formModal"); // Modal de formulario

  // Asegurar que el modal esté oculto al cargar la página
  formModal.hide();

  // Abrir modal al hacer clic en "Add Zone"
  $("#openFormModal").on("click", function () {
    formModal.fadeIn();
  });

  // Cerrar modal al hacer clic en "Cancel" o fuera del modal
  $("#closeFormModal, #formModal").on("click", function (event) {
    if (
      event.target === formModal[0] ||
      $(event.target).hasClass("cancel-btn")
    ) {
      formModal.fadeOut();
    }
  });
});

$(document).ready(function () {
  var formModal = $("#formModal"); // Modal del formulario

  // Asegurar que el modal esté oculto al cargar la página
  formModal.hide();

  // Abrir modal al hacer clic en "Add ODB(Splitter)"
  $("#openFormModal").on("click", function () {
    formModal.fadeIn();
  });

  // Cerrar modal al hacer clic en "Cancel" o fuera del modal
  $("#closeFormModal, #formModal").on("click", function (event) {
    if (
      event.target === formModal[0] ||
      $(event.target).hasClass("cancel-btn")
    ) {
      formModal.fadeOut();
    }
  });
});

$(document).ready(function () {
  var formModal = $("#formModalOLT"); // Modal del formulario OLT

  // Ocultar modal al cargar la página
  formModal.hide();

  // Abrir modal al hacer clic en "Add OLT"
  $("#openFormModalOLT").on("click", function () {
    formModal.fadeIn();
  });

  // Cerrar modal al hacer clic en "Cancel" o fuera del modal
  $("#closeFormModalOLT, #formModalOLT").on("click", function (event) {
    if (
      event.target === formModal[0] ||
      $(event.target).hasClass("cancel-btn")
    ) {
      formModal.fadeOut();
    }
  });

  // Acción para el botón "Test Connection"
  $(".test-connection").on("click", function () {
    alert(
      "Testing connection... (Aquí puedes agregar la lógica para la prueba)"
    );
  });
});
//EDIT OLT
$(document).ready(function () {
  // Variables para el modal
  var formModalEditOLT = $("#formModalEditOLT"); // Modal de edición de OLT

  // Asegurarse de que el modal esté oculto al cargar la página
  formModalEditOLT.hide();

  // Abrir modal al hacer clic en "Edit OLT settings"
  $("#editOltBtn").on("click", async function () {
    const oltId = getUrlParameter("id"); // Asegúrate de pasar el parámetro "olt" en la URL
    try {
      const response = await fetch(`../api/oltProfile.php?accion=one&olt=${oltId}`);
      if (!response.ok) {
        throw new Error(`Response status: ${response.status}`);
      }
  
      const data = await response.json();
      if (data.status === true) {
        // Obtén la información del OLT de la respuesta
        const olt = data.message;
        $("#olt_name").val(olt.OltName);
        $("#olt_ip").val(olt.OltIpPrivate);
        $("#telnet_port").val(olt.OltTelnetPort);
        $("#telnet_user").val(olt.UserTelnet);
        $("#telnet_password").val(olt.PassTelnet);
        $("#snmp_ro").val(olt.ReadComm);
        $("#snmp_rw").val(olt.WriteComm);
        $("#snmp_port").val(olt.OltSnmpPort);
        $("#hardware_version").val(olt.OltHardVer);
        $("#software_version").val(olt.SoftVer);
      } else {
        console.error("No se pudo obtener la información del OLT");
      }
    } catch (error) {
      console.error("Error al obtener los datos:", error);
    }
    // Función para obtener parámetros de la URL
    function getUrlParameter(name) {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get(name);
    }
    formModalEditOLT.fadeIn(); // Mostrar el modal con fadeIn
  });
  //SUBMIT FORM
  $("#editOltProfile").on("submit", async function (e) {
    e.preventDefault();
    const oltId = getUrlParameter("id");
    const form = new FormData($("#editOltProfile")[0]);

    const formData = {};
    form.forEach((value, key) => {
      formData[key] = value;
    });
    formData["id"] = oltId;
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
      // Realizar la solicitud fetch y esperar la respuesta
      const response = await fetch("../api/oltProfile.php", {
        method: "PUT",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(formData), // Los datos del formulario van en el body de la solicitud
      });

      // Verificamos si la respuesta es exitosa
      if (!response.status) {
        console.log("Error");
        throw new Error("Error en la solicitud");
      }

      // Convertimos la respuesta en formato JSON
      const data = await response.json();

      // Ejecutamos el código solo si la respuesta es exitosa
      if (data.status) {
        location.reload();
      }
    } catch (error) {
      // Si ocurre un error, lo capturamos y mostramos un mensaje
      console.error(error);
    }
    function getUrlParameter(name) {
      const urlParams = new URLSearchParams(window.location.search);
      return urlParams.get(name);
    }
  });
  // Cerrar el modal al hacer clic en el botón "Cancel" o fuera del modal
  $("#closeFormModal, #formModalEditOLT").on("click", function (event) {
    if (
      event.target === formModalEditOLT[0] ||
      $(event.target).hasClass("cancel-btn")
    ) {
      formModalEditOLT.fadeOut(); // Ocultar el modal con fadeOut
    }
  });

  // Acción para el botón "Test Connection"
  $(".test-connection").on("click", function () {
    alert(
      "Testing connection... (Aquí puedes agregar la lógica para la prueba)"
    );
  });
});
//SPEED PROFILE
$(document).ready(function () {
  var uploadTable, downloadTable;

  function initializeUploadTable() {
    if (!$.fn.DataTable.isDataTable("#speed_profileTable_upload")) {
      uploadTable = $("#speed_profileTable_upload").DataTable();
    }
  }

  function initializeDownloadTable() {
    if (!$.fn.DataTable.isDataTable("#speed_profileTable_download")) {
      downloadTable = $("#speed_profileTable_download").DataTable();
    }
  }

  function switchTab(
    activeButton,
    inactiveButton,
    showContainer,
    hideContainer
  ) {
    $(showContainer).show();
    $(hideContainer).hide();
    $(activeButton).addClass("active-tab");
    $(inactiveButton).removeClass("active-tab");
  }

  $("#btnDownload").on("click", function () {
    switchTab(
      "#btnDownload",
      "#btnUpload",
      "#downloadContainer",
      "#uploadContainer"
    );
  });

  $("#btnUpload").on("click", function () {
    switchTab(
      "#btnUpload",
      "#btnDownload",
      "#uploadContainer",
      "#downloadContainer"
    );
    initializeUploadTable();
  });

  // Mostrar Download por defecto con el color activo
  switchTab(
    "#btnDownload",
    "#btnUpload",
    "#downloadContainer",
    "#uploadContainer"
  );
  initializeDownloadTable();

  // **Detectar botón "Delete" dentro de las tablas y abrir el modal correspondiente**
  $(".content").on("click", "td button", ".deleteSpeedOlt", function () {
    if ($(this).text().trim() === "Delete") {
      let speed = $(this).data("speed");
      let olt = $(this).data("olt");
      let accion = $(this).data("accion");

      let speedProfileName = $(this).closest("tr").find("td").eq(0).text(); // Obtener nombre del perfil
      let tableId = $(this).closest("table").attr("id"); // Identificar de qué tabla viene

      if (tableId === "speed_profileTable_download") {
        $(".yesDeleteBtn")[0].dataset.profileId = speed;
        $(".yesDeleteBtn")[0].dataset.oltId = olt;
        $(".yesDeleteBtn")[0].dataset.accionId = accion;
        $("#profileNameDown").text(speedProfileName); // Insertar nombre en el modal de Download
        $("#modalDeletespeedprofileDown").fadeIn(); // Mostrar el modal de Download
      } else if (tableId === "speed_profileTable_upload") {
        $(".yesDeleteBtn")[1].dataset.profileId = speed;
        $(".yesDeleteBtn")[1].dataset.oltId = olt;
        $(".yesDeleteBtn")[1].dataset.accionId = accion;
        $("#profileNameUp").text(speedProfileName); // Insertar nombre en el modal de Upload
        $("#modalDeletespeedprofileUp").fadeIn(); // Mostrar el modal de Upload
      }
    }
  });

  // **Cerrar los modales cuando se presiona "No"**
  $(".noDeleteBtn").on("click", function () {
    $(".modal").fadeOut();
  });
  // **Cerrar los modales cuando se presiona "Si"**
  $(".yesDeleteBtn").on("click", async function () {
    Swal.fire({
      title: 'Eliminando',
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
      var speed = this.dataset.profileId;
      var olt = this.dataset.oltId;
      var accion = this.dataset.accionId;

      const Data = {
        profile: speed,
        olt: olt,
        accion: accion,
      };

      // Realizar la solicitud fetch y esperar la respuesta
      const response = await fetch(
        `../api/speedProfile.php?profile=${speed}&olt=${olt}`,
        {
          method: "DELETE",
          headers: {
            "Content-Type": "application/x-www-form-urlencoded",
          },
        }
      );

      // Verificamos si la respuesta es exitosa
      if (!response.status) {
        console.log("Error");
        throw new Error("Error en la solicitud");
      }

      // Convertimos la respuesta en formato JSON
      const data = await response.json();

      // Ejecutamos el código solo si la respuesta es exitosa
      if (data.status) {
        $(".modal").fadeOut();
        // Recargar la página
        location.reload();
      }
    } catch (error) {
      // Si ocurre un error, lo capturamos y mostramos un mensaje
      console.error(error);
    }
  });
  // **Cerrar modales cuando se presiona fuera del contenido**
  $(".modal").on("click", function (e) {
    if ($(e.target).is(".modal")) {
      $(this).fadeOut();
    }
  });
});
//SUBMIT SPEED PROFILE
$(document).ready(function () {
  // Mostrar el modal al hacer clic en "Add speed profile"
  $(".settings_header button").on("click", function () {
    $("#formModal").fadeIn(); // Usamos fadeIn para un efecto suave
  });

  // Ocultar el modal al hacer clic en "Cancel"
  $("#closeFormModal").on("click", function () {
    $("#formModal").fadeOut();
  });

  // También ocultar el modal si el usuario hace clic fuera del contenido
  $("#formModal").on("click", function (e) {
    if ($(e.target).is("#formModal")) {
      $("#formModal").fadeOut();
    }
  });
  // Cuando realiza submit el form en speedForm
  $("#speedFormAdd").on("submit", async function (e) {
    e.preventDefault();

    const formData = new FormData($("#speedFormAdd")[0]);
    Swal.fire({
      title: 'Insertando',
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
      const response = await fetch("../api/speedProfile.php", {
        method: "POST",
        body: formData, // Los datos del formulario van en el body de la solicitud
      });

      // Verificamos si la respuesta es exitosa
      if (!response.status) {
        console.log("Error");
        throw new Error("Error en la solicitud");
      }

      // Convertimos la respuesta en formato JSON
      const data = await response.json();

      // Ejecutamos el código solo si la respuesta es exitosa
      if (data.status) {
        // Recargar la página
        location.reload();
      }
    } catch (error) {
      // Si ocurre un error, lo capturamos y mostramos un mensaje
      console.error(error);
    }
  });
});

$(document).ready(function () {
  $(".settings_header button").on("click", function () {
    $("#modaladdIPs").fadeIn(); // Usamos fadeIn para un efecto suave
  });
});

const formIpGenerator = document.getElementById("IpGenerator");

// Obtener referencias a los inputs
const octet1 = document.getElementById("Octet1");
const octet2 = document.getElementById("Octet2");
const octet3 = document.getElementById("Octet3");
const octet4 = document.getElementById("Octet4");
const endOctet1 = document.getElementById("endOctet1");
const endOctet2 = document.getElementById("endOctet2");
const endOctet3 = document.getElementById("endOctet3");
const endOctet4 = document.getElementById("endOctet4");
const gateOctet1 = document.getElementById("gateOctet1");
const gateOctet2 = document.getElementById("gateOctet2");
const gateOctet3 = document.getElementById("gateOctet3");
const gateOctet4 = document.getElementById("gateOctet4");
const dns1 = document.getElementById("dns1");
const dns2 = document.getElementById("dns2");

//Input de mascara
const mask = document.getElementById("mask");

// Sincronizar los octetos cuando cambien los valores iniciales
octet1.addEventListener("input", syncOctets);
octet2.addEventListener("input", syncOctets);
octet3.addEventListener("input", syncOctets);

function syncOctets() {
  // Copiar valores a los campos de End IP
  endOctet1.value = octet1.value;
  endOctet2.value = octet2.value;
  endOctet3.value = octet3.value;

  // Deshabilitar los campos para que no se puedan editar
  endOctet1.disabled = true;
  endOctet2.disabled = true;

  gateOctet1.value = octet1.value;
  gateOctet2.value = octet2.value;
  gateOctet3.value = octet3.value;
}

// Manejar el envío del formulario
formIpGenerator.addEventListener("submit", async function (event) {
  event.preventDefault();

  // Asegurarse que los octetos estén sincronizados
  syncOctets();
  const startIp = [
    parseInt(octet1.value),
    parseInt(octet2.value),
    parseInt(octet3.value),
    parseInt(octet4.value),
  ].join(".");

  const endIp = [
    parseInt(endOctet1.value),
    parseInt(endOctet2.value),
    parseInt(endOctet3.value),
    parseInt(endOctet4.value),
  ].join(".");

  const subnetMask = mask.value;

  const defGate = [
    parseInt(gateOctet1.value),
    parseInt(gateOctet2.value),
    parseInt(gateOctet3.value),
    parseInt(gateOctet4.value),
  ].join(".");

  const firstDns = dns1.value;
  const secDns = dns2.value;

  //Validacion para los valores del start ip segment
  if (octet1.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      octet1.value
    );
    return;
  } else if (octet2.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      octet2.value
    );
    return;
  } else if (octet3.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      octet3.value
    );
    return;
  } else if (octet4.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      octet4.value
    );
  }

  //Validacion para los valores del end ip segment
  if (endOctet1.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      endOctet1.value
    );
    return;
  } else if (endOctet2.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      endOctet2.value
    );
    return;
  } else if (endOctet3.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      endOctet3.value
    );
    return;
  } else if (endOctet4.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      endOctet4.value
    );
    return;
  }

  //Validacion para valores del default gateway
  if (gateOctet1.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      gateOctet1.value
    );
    return;
  } else if (gateOctet2.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      gateOctet2.value
    );
    return;
  } else if (gateOctet3.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      gateOctet3.value
    );
    return;
  } else if (gateOctet4.value > 255) {
    alert(
      "No puedes agregar un valor mayor a 255. Error en el octeto => " +
      gateOctet4.value
    );
    return;
  }

  // Generar lista de IPs
  const ipList = generarRangoIPs(startIp, endIp);

  const ipData = {
    ip_range: ipList,
    mask: subnetMask,
    default_gateway: defGate,
    first_dns: firstDns,
    second_dns: secDns,
    olt_id: document.getElementById("olt_id").value, // Esta es la única línea añadida
  };

  console.log(ipData);
  try {
    Swal.fire({
      text: "Por favor espera un momento",
      allowOutsideClick: false,
      allowEscapeKey: false,
      didOpen: () => {
        Swal.showLoading();
      },
      background: "#1a1a1a",
      color: "#fff",
      customClass: {
        popup: "my-loading-popup",
        title: "my-loading-title",
      },
    });
    // Enviar datos al servidor
    const response = await fetch("../app/metodos/ipGenerator/IPs.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify(ipData),
    });

    const result = await response.json();
    Swal.close();

    if (response.ok) {
      await Swal.fire({
        icon: "success",
        title: "Éxito",
        text: "IPs generadas exitosamente!",
        background: "#1a1a1a",
        color: "#fff",
      });
      const urlParams = new URLSearchParams(window.location.search);
      const id = urlParams.get("id"); // Obtiene el valor de "id"
      window.location.href = `ONU_MgmtIPs.php?id=${id}`;
    } else {
      throw new Error(result.message || "Error al guardar los datos");
    }
  } catch (error) {
    Swal.close(); // Cerrar loader si hay error
    await Swal.fire({
      icon: "error",
      title: "Error",
      text: "Error al enviar los datos: " + error.message,
      background: "#1a1a1a",
      color: "#fff",
    });
  }
  // Aquí puedes hacer lo que necesites con ipList, como mostrarla en pantalla
});

function generarRangoIPs(ipInicio, ipFin) {
  const listaIPs = [];
  const inicio = ipInicio.split(".").map(Number);
  const fin = ipFin.split(".").map(Number);

  // Validar formato de IPs
  if (inicio.length !== 4 || fin.length !== 4) {
    console.error("Formato de IP inválido");
    return [];
  }

  let actual = [...inicio];

  while (compararIPs(actual, fin) <= 0) {
    listaIPs.push(actual.join("."));

    // Incrementar la IP
    actual[3]++;

    // Manejar el desbordamiento (cuando llega a 255)
    for (let i = 3; i > 0; i--) {
      if (actual[i] > 255) {
        actual[i] = 0;
        actual[i - 1]++;
      }
    }

    // Verificar si hemos pasado la IP final
    if (compararIPs(actual, fin) > 0) {
      break;
    }
  }

  return listaIPs;
}

function compararIPs(ip1, ip2) {
  for (let i = 0; i < 4; i++) {
    if (ip1[i] < ip2[i]) return -1;
    if (ip1[i] > ip2[i]) return 1;
  }
  return 0;
}
$(document).ready(async function () {
  // Si no está inicializada la tabla, inicializarla
  if (!$.fn.DataTable.isDataTable('#ipsTable')) {
      const searchParams = new URLSearchParams(window.location.search);
      const olt = searchParams.get('id');
      try {
          const response = await fetch(`../api/ips.php?accion=ipsTable&zona=${olt}`);
          if (!response.ok) {
              throw new Error(`Response status: ${response.status}`);
            }
        
            const data = await response.json();
            if (data.status) {
              // Verificamos si DataTable ya está inicializado y destruimos la instancia anterior si es necesario
              if ($.fn.DataTable.isDataTable('#ipsTable')) {
                  $('#ipsTable').DataTable().destroy();  // Destruir la instancia anterior
              }
              // Primero, limpiar el tbody antes de insertar nuevos datos
              $("#ipsTableBody").empty();
              // Iterar sobre los datos y agregar filas a la tabla
              data.ip.forEach(item => {

                  const row = $('<tr>');
                  row.html(`
                      <td>${item.ipAddress}</td>
                      <td>${item.mask}</td>
                      <td>${item.defaultGateway}</td>
                      <td>${item.firstDns}</td>
                      <td>${item.secDns}</td>
                      <td><div class="action-buttons">
                            <button class="action-btn edit-btn" data-id="${item.id_Ip}">
                                <i class="fas fa-edit">Delete</i>
                            </button>
                        </div></td>
                      `);

                  $("#ipsTableBody").append(row);
              });

              // Ahora inicializar DataTable solo si aún no está inicializada
              downloadTable = $("#ipsTable").DataTable({
                  paging: true,
                  searching: true,
                  ordering: true,
                  info: true,
                  lengthMenu: [5, 10, 25, 50],
                  pageLength: 5,
                  language: {
                      lengthMenu: "Mostrar _MENU_ registros por página",
                      zeroRecords: "No se encontraron resultados",
                      info: "Mostrando página _PAGE_ de _PAGES_",
                      infoEmpty: "No hay registros disponibles",
                      infoFiltered: "(filtrado de _MAX_ registros totales)",
                      search: "Buscar:",
                      paginate: {
                          first: "Primero",
                          last: "Último",
                          next: "Siguiente",
                          previous: "Anterior"
                      }
                  }
              });
          } else {
              console.log('No se encontraron datos para Download');
          }
      } catch (error) {
          console.error('Error al obtener datos de Download:', error);
      }
  }
});