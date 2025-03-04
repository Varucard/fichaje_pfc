document.getElementById('fichaje_manual').addEventListener('click', function() {

  var dni_user = prompt('Ingrese el DNI del Usuario:');

  if (dni_user === null) {
    // Usuario canceló el prompt
    return;
  }

  // Validar que el DNI sea un número y no esté vacío
  if (dni_user.trim() === '' || isNaN(dni_user)) {
    alert('Ingrese un DNI válido por favor.');
  } else {
    // Redirigir si el DNI es válido
    window.location.href = '../controllers/nuevo_fichaje_manual.php?dni_user=' + encodeURIComponent(dni_user);
  }
});
