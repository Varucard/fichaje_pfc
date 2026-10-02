/*
 * Tooltip del gráfico de caja: al pasar el mouse o enfocar con teclado una columna.
 */
(function () {
  'use strict';

  const figura = document.querySelector('.grafico-caja');
  if (!figura) return;
  const tooltip = figura.querySelector('.grafico-tooltip');

  function mostrar(columna) {
    tooltip.textContent = columna.dataset.tooltip;
    tooltip.hidden = false;
    const caja = figura.getBoundingClientRect();
    // Se ubica sobre la barra (no sobre toda la columna, que ocupa todo el alto).
    const col = (columna.querySelector('.segmento') ?? columna).getBoundingClientRect();
    const izquierda = Math.min(Math.max(col.left - caja.left + col.width / 2 - tooltip.offsetWidth / 2, 0), caja.width - tooltip.offsetWidth);
    tooltip.style.left = `${izquierda}px`;
    tooltip.style.top = `${Math.max(col.top - caja.top - tooltip.offsetHeight - 8, 0)}px`;
  }

  figura.querySelectorAll('.columna').forEach((columna) => {
    columna.addEventListener('mouseenter', () => mostrar(columna));
    columna.addEventListener('focus', () => mostrar(columna));
    columna.addEventListener('mouseleave', () => (tooltip.hidden = true));
    columna.addEventListener('blur', () => (tooltip.hidden = true));
  });
})();
