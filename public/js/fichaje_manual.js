document.getElementById('fichaje_manual').addEventListener('click', function() {
  var dni_user = prompt('Ingrese el DNI del Usuario:');
  if (dni_user) {
    // Validar que DNI sea un numero
    if (!isNaN(dni_user)) {
      window.location.href = '../controllers/nuevo_fichaje_manual.php?dni_user=' + encodeURIComponent(dni_user);
    }
  } else {
    alert('Ingrese un DNI valido por favor');
  }
});
