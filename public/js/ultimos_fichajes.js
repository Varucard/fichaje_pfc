function formatDate(dateString) {
  const date = new Date(dateString);
  const day = ('0' + date.getDate()).slice(-2);
  const month = ('0' + (date.getMonth() + 1)).slice(-2);
  const year = date.getFullYear();
  return `${day}-${month}-${year}`;
}

function actualizarFichajes() {
  fetch('../controllers/ultimos_fichajes_controller.php')
    .then(response => response.json())
    .then(data => {
      const tbody = document.querySelector('#tabla-fichajes tbody');
      const tabla = document.getElementById('contenedor-tabla');
      const mensaje = document.getElementById('mensaje-vacio');

      tbody.innerHTML = '';

      if (data.length === 0) {
        tabla.style.display = 'none';
        mensaje.style.display = 'block';
        return;
      }

      // Si hay datos
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

      mensaje.style.display = 'none';
      tabla.style.display = 'block';
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

setInterval(actualizarFichajes, 5000);

document.addEventListener('DOMContentLoaded', actualizarFichajes);
