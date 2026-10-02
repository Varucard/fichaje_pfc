/*
 * Tabla de últimos ingresos: se refresca cada 5 segundos.
 */
(function () {
  'use strict';

  const INTERVALO_MS = 5000;
  const tbody = document.querySelector('#tabla-fichajes tbody');
  const contenedor = document.getElementById('contenedor-tabla');
  const vacio = document.getElementById('mensaje-vacio');
  if (!tbody) return;

  const dosDigitos = (n) => String(n).padStart(2, '0');

  function formatear(valor, conHora) {
    if (!valor) return '-';
    // "2025-03-01" o "2025-03-01 23:16:42" (hora local del servidor)
    const [fecha, hora = ''] = valor.split(' ');
    const [anio, mes, dia] = fecha.split('-');
    return `${dia}-${mes}-${anio}` + (conHora && hora ? ` ${hora}` : '');
  }

  function celda(texto, clase) {
    const td = document.createElement('td');
    td.textContent = texto ?? '';
    if (clase) td.className = clase;
    return td;
  }

  function dibujar(fichajes) {
    const vacios = fichajes.length === 0;
    contenedor.classList.toggle('oculto', vacios);
    vacio.classList.toggle('oculto', !vacios);

    tbody.replaceChildren(...fichajes.map((f) => {
      const fila = document.createElement('tr');
      const alerta = f.pago_cerca ? 'cerca-de-vencer' : '';
      fila.append(
        celda(f.rfid),
        celda(f.dni, alerta),
        celda(f.alumno, 'diminuto'),
        celda(formatear(f.addmission_date, true), 'diminuto'),
        celda(formatear(f.date_of_renovation, false), alerta),
      );
      return fila;
    }));
  }

  function actualizar() {
    fetch(PFC.url('/api/fichajes/ultimos'), { headers: { Accept: 'application/json' } })
      .then((r) => (r.ok ? r.json() : Promise.reject(r.status)))
      .then(dibujar)
      .catch((error) => console.error('No se pudieron cargar los fichajes:', error));
  }

  actualizar();
  setInterval(actualizar, INTERVALO_MS);
})();
