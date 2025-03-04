document.getElementById('cargar_alumno_profesor').addEventListener('click', function() {

  var id_clase = document.getElementById('id').value;
  var dni_user = prompt('Ingrese el DNI del alumno/ profesor');

  if (dni_user === null) {
    // Usuario canceló el prompt
    return;
  }

  // Validar que el DNI sea un número y no esté vacío
  if (dni_user.trim() === '' || isNaN(dni_user)) {
    alert('Ingrese un DNI válido por favor.');
  } else {
    // Redirigir si el DNI es válido
    window.location.href = '../controllers/alta_alumno_profesor_clase_controller.php?dni_user=' + encodeURIComponent(dni_user) + '&id_clase=' + encodeURIComponent(id_clase);
  }
});
