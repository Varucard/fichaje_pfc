function mostrarRFIDDesconocidos() {
  fetch('../controllers/uid_desconocido_controller.php')
    .then(response => response.json())  // Convierte la respuesta en JSON
    .then(data => {

      if (data.status == 'ok' && data.rfid_desconocidos.length != 0) {
        console.log(data);
        // Muestra el mensaje en un alert si hay un mensaje en la respuesta
        alert('Nuevo llavero desconocido: ' + data.rfid_desconocidos[0]['uid']);
      }
    })
    .catch(error => {
      console.error('Error al cargar los UIDs desconocidos:', error);
    });
}

setInterval(mostrarRFIDDesconocidos, 5000);

// Llamamos a la función cuando la página cargue
document.addEventListener('DOMContentLoaded', mostrarRFIDDesconocidos);
