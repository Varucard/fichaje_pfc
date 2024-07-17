document.getElementById('busqueda_usuario').addEventListener('keyup', function(event) {
  if (event.key === 'Enter') {
    const busqueda = event.target.value.trim(); // Elimina espacios en blanco alrededor del valor
    let tipoBusqueda;

    // TODO: Mejorar todo lo relacionado a la busqueda, validaciones, formatos, etc.
    if (/^\d{7,8}$/.test(busqueda)) { // Verificar si es un DNI: números de 7 u 8 dígitos
      tipoBusqueda = 'dni';
    } else if (/^[a-zA-Z0-9]+$/.test(busqueda)) { // Verificar si es un RFID: 
      tipoBusqueda = 'rfid';
    } else {
      tipoBusqueda = 'name'; // Si no es nada de lo otro es un nombre
    }

    window.location.href = `../controllers/busqueda_usuario_controller.php?busqueda=${encodeURIComponent(busqueda)}&tipo_busqueda=${tipoBusqueda}`;
  }
});
