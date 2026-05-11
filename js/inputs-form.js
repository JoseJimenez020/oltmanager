const apellidosInput = document.querySelector('input[name="apellidos"]');

apellidosInput.addEventListener('keypress', function(event) {
  const charCode = (event.which) ? event.which : event.keyCode;

  // Permitir letras mayúsculas y minúsculas, espacios y algunos símbolos comunes (acentos, ñ, etc.)
  const allowedCharacters = /[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]/;
  const characterPressed = String.fromCharCode(charCode);

  if (allowedCharacters.test(characterPressed)) {
    return true; // Permitir la entrada
  } else {
    event.preventDefault(); // Evitar la entrada
    return false;
  }
});

const nombreInput = document.querySelector('input[name="name"]');

nombreInput.addEventListener('keypress', function(event) {
  const charCode = (event.which) ? event.which : event.keyCode;

  // Permitir letras mayúsculas y minúsculas, espacios y algunos símbolos comunes (acentos, ñ, etc.)
  const allowedCharacters = /[a-zA-Z\sáéíóúÁÉÍÓÚñÑ]/;
  const characterPressed = String.fromCharCode(charCode);

  if (allowedCharacters.test(characterPressed)) {
    return true; // Permitir la entrada
  } else {
    event.preventDefault(); // Evitar la entrada
    return false;
  }
});