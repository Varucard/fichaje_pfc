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
      PFC.copiar(uid)
        .then(() => (copiar.textContent = '¡Copiado!'))
        .catch(() => (copiar.textContent = 'Copiá a mano: ' + uid));
    });
    contenido.append(copiar);

    PFC.avisar(contenido);
    PFC.sonar('alerta');
  }

  PFC.sondear(() => PFC.json('/api/llaveros/pendientes').then((datos) => (datos.llaveros ?? []).forEach(avisar)), INTERVALO_MS);
})();
