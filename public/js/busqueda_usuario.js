document.getElementById('busqueda_usuario').addEventListener('keyup', function(event) {
  if (event.key === 'Enter') {
    const busqueda = event.target.value;
    let tipoBusqueda;

    if (!isNaN(busqueda)) {
      tipoBusqueda = 'dni';
    } else if (/^[a-zA-Z0-9]+$/.test(busqueda)) {
      tipoBusqueda = 'rfid';
    } else {
      tipoBusqueda = 'name';
    }

    window.location.href = `../controllers/busqueda_usuario_controller.php?busqueda=${busqueda}&tipo_busqueda=${tipoBusqueda}`;
  }
});
