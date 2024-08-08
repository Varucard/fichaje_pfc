document.getElementById('busqueda_fichaje').addEventListener('keyup', function(event) {
  if (event.key === 'Enter') {
    const busqueda = event.target.value.trim(); // Elimina espacios en blanco alrededor del valor

    if (!(/^[A-Za-z\s]+$/.test(busqueda) || (/^\d{7,8}$/.test(busqueda)))) {
      alert('Por favor, no ingrese valores erroneos');
      exit();
    } 

    window.location.href = `../controllers/busqueda_fichaje_controller.php?busqueda=${encodeURIComponent(busqueda)}`;
  }
});
