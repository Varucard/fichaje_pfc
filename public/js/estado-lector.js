/*
 * Indicador del lector en el dashboard: consulta si el Arduino responde y cuándo fue la
 * última lectura. Se actualiza cada minuto.
 */
(function () {
  'use strict';

  const indicador = document.getElementById('estado-lector');
  if (!indicador) return;

  function haceCuanto(fecha) {
    if (!fecha) return 'sin lecturas registradas';
    const minutos = Math.round((Date.now() - new Date(fecha.replace(' ', 'T')).getTime()) / 60000);
    if (minutos < 1) return 'última lectura hace instantes';
    if (minutos < 60) return `última lectura hace ${minutos} min`;
    if (minutos < 1440) return `última lectura hace ${Math.round(minutos / 60)} h`;
    return `última lectura hace ${Math.round(minutos / 1440)} día(s)`;
  }

  function mostrar(estado) {
    indicador.classList.remove('en-linea', 'sin-respuesta', 'sin-configurar');
    let texto;
    if (!estado.configurado) {
      indicador.classList.add('sin-configurar');
      texto = 'Lector: sin configurar (IP_ARDUINO)';
    } else if (estado.en_linea) {
      indicador.classList.add('en-linea');
      texto = `Lector en línea (${estado.latencia_ms} ms) · ${haceCuanto(estado.ultima_lectura)}`;
    } else {
      indicador.classList.add('sin-respuesta');
      texto = `Lector SIN RESPUESTA (${estado.ip}) · ${haceCuanto(estado.ultima_lectura)}`;
    }
    indicador.replaceChildren(Object.assign(document.createElement('span'), { className: 'punto' }), ' ' + texto);
  }

  function consultar() {
    PFC.json('/api/lector/estado').then(mostrar).catch((error) => console.error('Estado del lector:', error));
  }

  consultar();
  setInterval(consultar, 60000);
})();
