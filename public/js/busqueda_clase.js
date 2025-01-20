document.getElementById('busqueda_clase').addEventListener('keyup', function(event) {
  if (event.key === 'Enter') {
    const busqueda = event.target.value.trim();

    window.location.href = `../controllers/busqueda_clase_controller.php?busqueda=${encodeURIComponent(busqueda)}`;
  }
});
