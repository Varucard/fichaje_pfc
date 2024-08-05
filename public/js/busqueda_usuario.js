document.getElementById('busqueda_usuario').addEventListener('keyup', function(event) {
  if (event.key === 'Enter') {
    const busqueda = event.target.value.trim(); // Elimina espacios en blanco alrededor del valor
    let tipoBusqueda;

    if (/^\d{7,8}$/.test(busqueda)) { // Verificar si es un DNI: números de 7 u 8 dígitos
      tipoBusqueda = 'dni';
    } else if (/^[A-Za-z\s]+$/.test(busqueda)) {
      tipoBusqueda = 'name';
    } 
    else {
      alert('Por favor, no ingrese valores erroneos');
      exit();
    }

    window.location.href = `../controllers/busqueda_usuario_controller.php?busqueda=${encodeURIComponent(busqueda)}&tipo_busqueda=${tipoBusqueda}`;
  }
});
