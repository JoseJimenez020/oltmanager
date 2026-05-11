const formAutorizar = document.getElementById("formAuth");

formAutorizar.addEventListener("submit", async function (event) {
  event.preventDefault();

  const formData = new FormData(formAutorizar);
  const formJson = Object.fromEntries(formData.entries());

  try {
    // Mostrar alerta de carga
    Swal.fire({
      title: 'Cargando...',
      text: 'Por favor espera',
      allowOutsideClick: false,
      background: '#1a1a1a',
      color: '#fff',
      allowEscapeKey: false,
      didOpen: () => {
        Swal.showLoading();

      }
    });

    const response = await fetch("../api/onuProfile.php", {
      method: "POST",
      headers: {
        'Content-Type': 'application/json'
      },
      body: JSON.stringify(formJson),
    });

    // Verificar si hubo error en el fetch
    if (!response.ok) {
      throw new Error("Error en la solicitud");
    }

    const data = await response.json();

    // Cerrar el loading
    Swal.close();

    if (data.ok) {
      // Mostrar mensaje de éxito
      Swal.fire({
        icon: 'success',
        title: 'Éxito',
        text: 'Tarea completada correctamente',
        timer: 2000,
        showConfirmButton: false,
      });

      window.location.href = `../views/ont.php?id=${data.id}`;
    } else {
      // Mostrar mensaje de error si la respuesta dice que no
      Swal.fire({
        icon: 'error',
        title: 'Ups...',
        text: data.message || 'Ocurrió un error inesperado',
      });
    }

  } catch (error) {
    // Cerrar el loading en caso de error
    Swal.close();

    Swal.fire({
      icon: 'error',
      title: 'Error',
      text: error.message,
    });
  }
});
