/*
 * Avisa cuando el lector detecta un llavero que no está asignado a nadie,
 * con un botón para copiar el código y cargarlo en un usuario.
 */
(function () {
  'use strict';

  const INTERVALO_MS = 5000;

  function avisar(uid) {
    const contenido = document.createElement('span');
    contenido.append('Nuevo llavero desconocido: ');

    const codigo = document.createElement('strong');
    codigo.textContent = uid;
    contenido.append(codigo, ' ');

    const copiar = document.createElement('button');
    copiar.type = 'button';
    copiar.className = 'button_small';
    copiar.textContent = 'Copiar';
    copiar.addEventListener('click', () => {
      navigator.clipboard?.writeText(uid).then(() => (copiar.textContent = '¡Copiado!'));
    });
    contenido.append(copiar);

    PFC.avisar(contenido);
  }

  function consultar() {
    fetch(PFC.url('/api/llaveros/pendientes'), { headers: { Accept: 'application/json' } })
      .then((r) => (r.ok ? r.json() : Promise.reject(r.status)))
      .then((datos) => (datos.llaveros ?? []).forEach(avisar))
      .catch((error) => console.error('No se pudieron consultar los llaveros:', error));
  }

  consultar();
  setInterval(consultar, INTERVALO_MS);
})();
