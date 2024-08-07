function formatDate(dateString) {
  const date = new Date(dateString);
  const day = ('0' + date.getDate()).slice(-2);
  const month = ('0' + (date.getMonth() + 1)).slice(-2);
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
}

//** Contiene para evitar inconvenientes el checkeador de nuevos UID
function checkForNewUID() {
  fetch('../controllers/uid_desconocido_controller.php')
    .then(response => response.json())
    .then(data => {
      if (data.uid) {
        copyToClipboard(data.uid);
        alert(`Nuevo UID desconocido detectado: ${data.uid}`);
      }
    })
    .catch(error => console.error('Error fetching UID:', error));
}

//** Copia el UID desconocido al Portapapeles del Usuario
function copyToClipboard(text) {
  const dummy = document.createElement('textarea');
  document.body.appendChild(dummy);
  dummy.value = text;
  dummy.select();
  document.execCommand('copy');
  document.body.removeChild(dummy);
}

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
          <td class="diminuto">${fichaje.alumno}</td>
          <td class="diminuto">${formatDateTime(fichaje.addmission_date)}</td>
          <td class="${fichaje.pago_cerca ? 'cerca-de-vencer' : ''}">${formatDate(fichaje.date_of_renovation)}</td>
        `;
        tbody.appendChild(row);
      });
    })
    .catch(error => console.error('Error fetching fichajes:', error));
}

function formatDateTime(dateString) {
  const date = new Date(dateString);
  const day = ('0' + date.getDate()).slice(-2);
  const month = ('0' + (date.getMonth() + 1)).slice(-2);
  const year = date.getFullYear();
  const hours = ('0' + date.getHours()).slice(-2);
  const minutes = ('0' + date.getMinutes()).slice(-2);
  const seconds = ('0' + date.getSeconds()).slice(-2);
  return `${day}-${month}-${year} ${hours}:${minutes}:${seconds}`;
}

// Long polling
function longPolling() {
  actualizarFichajes();
  checkForNewUID();
  setTimeout(longPolling, 5000); // Poll every 5 seconds
}

document.addEventListener('DOMContentLoaded', () => {
  longPolling();
});
