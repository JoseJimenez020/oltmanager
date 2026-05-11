<script>
    document.addEventListener("DOMContentLoaded", function() {
        <?php if (isset($message)): ?>
            <?php if ($message === 'login'): ?>
                Swal.fire({
                    title: "Sesion Iniciada",
                    text: "Bienvenido <?php echo ($sesion['UsuarioCorreo']); ?>",
                    icon: "success"
                }).then(() => {
            window.location.href = 'views/index.php';
        });
            <?php elseif ($message === 'error'): ?>
                Swal.fire({
                    title: "Ha ocurrido un error",
                    text: "Correo electrónico o contraseña incorrectos.",
                    icon: "error"
                });
            <?php elseif ($message === 'existe'): ?>
                Swal.fire({
                    title: "Ha ocurrido un error",
                    text: "El usuario que estas intentando registrar ya existe.",
                    icon: "error"
                })
            <?php elseif ($message === 'registro'): ?>
                Swal.fire({
                    title: "Registro Exitoso",
                    text: "El usuario se ha registrado con exito.",
                    icon: "success"
                }).then(() => {
            window.location.href = './';
        });
            <?php endif; ?>
        <?php endif; ?>
    });

    function validarLogin() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailValue = emailInput.value;
    const passwordValue = passwordInput.value;

    // Validar correo electrónico
    if (!emailValue.endsWith('@fast-net.com.mx')) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Por favor introduzca un correo valido con dominio @fast-net.com.mx!"
        });
      return false; // Evitar que el formulario se envíe
    }

    // Validar contraseña
    if (passwordValue.length < 8) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Por favor introduzca una contraseña de almenos 8 caracteres!"
        });
      return false; // Evitar que el formulario se envíe
    }

    return true; // Permitir que el formulario se envíe
  }

  function validarRegistro() {
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');
    const emailValue = emailInput.value;
    const passwordValue = passwordInput.value;

    // Validar correo electrónico
    if (!emailValue.endsWith('@fast-net.com.mx')) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Por favor introduzca un correo valido con dominio @fast-net.com.mx!"
        });
      return false; // Evitar que el formulario se envíe
    }

    // Validar contraseña
    if (passwordValue.length < 8) {
        Swal.fire({
            icon: "error",
            title: "Error",
            text: "Por favor introduzca una contraseña de almenos 8 caracteres!"
        });
      return false; // Evitar que el formulario se envíe
    }

    return true; // Permitir que el formulario se envíe
  }

    function cerrarSesion() {
        Swal.fire({
            title: "Estas seguro que quieres cerrar sesion?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonColor: "#3085d6",
            cancelButtonColor: "#d33",
            confirmButtonText: "Si"
        }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
            title: "Sesión Cerrada",
            icon: "success"
            }).then(() =>{
                window.location.href = './funciones/logout.php';
            });
        }
        })
    }
    </script>