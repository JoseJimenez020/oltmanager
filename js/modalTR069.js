document.addEventListener('DOMContentLoaded', function () {
    // Elementos del modal TR069
    const modalTR069 = document.getElementById("modalTR069");
    const btnTR069 = document.getElementById("tr069");
    const formTR069 = document.getElementById("tr069Form");

    // Elementos de TR069
    const staticIp = document.getElementById("staticIp");
    const radioInactive = document.getElementById("radioInactive");
    const radioStaticIp = document.getElementById("radioStaticIp");

    // Elementos de VoIP
    const voipDisabled = document.getElementById("voipDisabled");
    const voipEnabled = document.getElementById("voipEnabled");
    const containerVoIP = document.getElementById("containerVoIP");
    const radioMgmt = document.getElementById("radioMgmt");
    const radioWan = document.getElementById("radioWan");

    // 1. Control del Modal TR069
    if (btnTR069 && modalTR069) {
        btnTR069.addEventListener('click', function () {
            modalTR069.classList.remove('classTR069');
            modalTR069.classList.add('classTR069Close');
        });

        modalTR069.addEventListener('click', function (e) {
            if (e.target === this) {
                modalTR069.classList.add('classTR069');
                modalTR069.classList.remove('classTR069Close');
            }
        });
    }

    // 2. Control de Radios TR069
    if (radioInactive && radioStaticIp && staticIp) {
        radioInactive.addEventListener('change', function () {
            staticIp.classList.add('containerStaticIp');
            staticIp.classList.remove('containerStaticIpClose');
        });

        radioStaticIp.addEventListener('change', function () {
            staticIp.classList.remove('containerStaticIp');
            staticIp.classList.add('containerStaticIpClose');
        });
    }

    if (voipDisabled && voipEnabled && containerVoIP) {
        // Estado inicial
        containerVoIP.style.display = voipDisabled.checked ? 'none' : 'flex';

        // Evento para ambos radios
        function handleVoipChange() {
            if (voipEnabled.checked) {
                containerVoIP.style.display = 'flex';
                containerVoIP.style.flexDirection = 'row';
                containerVoIP.style.gap = '30px';
            } else {
                containerVoIP.style.display = 'none';
            }
        }

        voipDisabled.addEventListener('change', handleVoipChange);
        voipEnabled.addEventListener('change', handleVoipChange);
    }

    // 4. Control de Radios VoIP Options
    if (radioMgmt && radioWan) {
        // Estado inicial ya está manejado por el HTML (checked)
        radioMgmt.addEventListener('change', function () {
            // Lógica adicional si es necesaria
        });

        radioWan.addEventListener('change', function () {
            // Lógica adicional si es necesaria
        });
    }
    formTR069.addEventListener("submit", async function (e) {
        e.preventDefault();
        const formData = new FormData(formTR069);
        const redata = Object.fromEntries(formData.entries());

        const checkbox = document.getElementById("access");
        redata.access = checkbox.checked ? "allow" : "default";
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
            body: JSON.stringify(redata), // Los datos del formulario van en el body de la solicitud
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
            responseDiv.innerHTML = "Hubo un error al enviar los datos.";
        }
    });
    btnTR069.addEventListener("click", function () {
        async function vlan(zona) {
            try {
                const response = await fetch(`../api/ips.php?accion=vlanMgmt&zona=${zona}`);
                if (!response.ok) {
                    throw new Error(`Response status: ${response.status}`);
                  }
              
                  const data = await response.json();
                  if (data.status && Array.isArray(data.message)) {
                    const select = document.querySelector("#tr069Form #mgmtVlan");
                    select.innerHTML = '';
                    data.message.forEach(item => {
                        const option = document.createElement("option");
                        option.value = item.VlanId;
                        option.textContent = item.Vlan;
                        select.appendChild(option);
                    });
                } else {
                    console.error("Respuesta inesperada del servidor:", data);
                }
            } catch (error) {
                console.error("Error al cargar los datos:", error);
            }
        }
        async function ip(zona) {
            try {
                const response = await fetch(`../api/ips.php?accion=ipMgmt&zona=${zona}`);
                if (!response.ok) {
                    throw new Error(`Response status: ${response.status}`);
                  }
              
                  const data = await response.json();
                  if (data.status && Array.isArray(data.message)) {
                    const select = document.querySelector("#tr069Form #mgmtIp");

                    // Limpiamos las opciones actuales excepto la primera
                    select.innerHTML = '<option value="null">Opcion</option>';

                    data.message.forEach(item => {
                        const option = document.createElement("option");
                        option.value = item.id_Ip;
                        option.textContent = item.ipAddress;
                        select.appendChild(option);
                    });
                } else {
                    console.error("Respuesta inesperada del servidor:", data);
                }
            } catch (error) {
                console.error("Error al cargar los datos:", error);
            }
        }
        async function onu(id) {
            try {
                const response = await fetch(`../api/onuProfile.php?accion=onu&id=${id}`);
                if (!response.ok) {
                    throw new Error(`Response status: ${response.status}`);
                  }
              
                  const data = await response.json();
                  if (data.status) {

                    vlan(`${data.message.OntOlt}`);
                    ip(`${data.message.OntOlt}`);
                } else {
                    console.error("Respuesta inesperada del servidor:", data);
                }
            } catch (error) {
                console.error("Error al cargar los datos:", error);
            }
        }
        const searchParams = new URLSearchParams(window.location.search);
        const id = searchParams.get('id');
        onu(id);
    });
});