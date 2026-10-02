/*
 * Cartel con los clientes que cumplen años hoy.
 */
(function () {
  'use strict';

  const modal = document.getElementById('cumpleanosModal');
  if (!modal) return;

  fetch(PFC.url('/api/usuarios/cumpleaneros'), { headers: { Accept: 'application/json' } })
    .then((r) => (r.ok ? r.json() : Promise.reject(r.status)))
    .then((usuarios) => {
      if (!usuarios.length) return;

      const nombres = usuarios.map((u) => `${u.user_name} ${u.user_surname ?? ''}`.trim()).join(', ');
      document.getElementById('cumpleanosTexto').textContent = `🎉 Hoy cumplen años: ${nombres}`;
      modal.style.display = 'block';

      modal.querySelector('.close').addEventListener('click', () => (modal.style.display = 'none'));
      window.addEventListener('click', (evento) => {
        if (evento.target === modal) modal.style.display = 'none';
      });
    })
    .catch((error) => console.error('No se pudieron cargar los cumpleaños:', error));
})();
