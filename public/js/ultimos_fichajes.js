function actualizarFichajes() {
  fetch('../controllers/ultimos_fichajes_controller.php')
    .then(response => response.json())
    .then(data => {
      const tbody = document.querySelector('#tabla-fichajes tbody');
      tbody.innerHTML = '';

      data.forEach(fichaje => {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td>${fichaje.rfid}</td>
          <td class="${fichaje.pago_cerca ? 'cerca-de-vencer' : ''}">${fichaje.dni}</td>
          <td>${fichaje.alumno}</td>
          <td>${fichaje.addmission_date}</td>
          <td class="${fichaje.pago_cerca ? 'cerca-de-vencer' : ''}">${fichaje.date_of_renovation}</td>
        `;
        tbody.appendChild(row);
      });
    })
    .catch(error => console.error('Error fetching fichajes:', error));
}

// Long polling
function longPolling() {
  actualizarFichajes();
  setTimeout(longPolling, 5000); // Poll every 5 seconds
}

document.addEventListener('DOMContentLoaded', () => {
  longPolling();
});