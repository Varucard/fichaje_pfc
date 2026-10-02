/*
 * Comportamiento común del panel.
 * - Buscadores: <input data-buscar="usuarios|fichajes|clases">
 * - Confirmaciones: <form data-confirmar="¿Seguro?">
 * - Botón volver: <button data-volver="/ruta-alternativa">
 * - Acciones rápidas: <button data-accion="fichaje-manual|pago-manual"> (abren una ventana <dialog>)
 */
(function () {
  'use strict';

  const meta = (nombre) => document.querySelector(`meta[name="${nombre}"]`)?.content ?? '';
  const BASE = meta('base-url');

  const PFC = (window.PFC = {
    url: (ruta) => BASE + ruta,

    /** Ruta actual relativa a la app, para volver después de una acción. */
    rutaActual: () => location.pathname.slice(BASE.length) + location.search || '/dashboard',

    /**
     * GET a un endpoint JSON del panel. Si la sesión venció (401), vuelve al login.
     * @returns {Promise<any>}
     */
    json(ruta) {
      return fetch(PFC.url(ruta), { headers: { Accept: 'application/json' }, credentials: 'same-origin' }).then((r) => {
        if (r.status === 401) {
          location.href = PFC.url('/login');
          return new Promise(() => {});
        }
        return r.ok ? r.json() : Promise.reject(new Error(`HTTP ${r.status}`));
      });
    },

    /** Envía un POST como formulario normal (incluye el token CSRF). */
    enviar(ruta, datos) {
      const form = document.createElement('form');
      form.method = 'post';
      form.action = PFC.url(ruta);
      Object.entries({ _token: meta('csrf-token'), ...datos }).forEach(([nombre, valor]) => {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = nombre;
        input.value = valor;
        form.appendChild(input);
      });
      document.body.appendChild(form);
      form.submit();
    },

    /** Muestra un aviso no bloqueante en la esquina de la pantalla. */
    avisar(contenido, { duracion = 0 } = {}) {
      const contenedor = document.getElementById('avisos');
      if (!contenedor) return;
      const aviso = document.createElement('div');
      aviso.className = 'aviso';
      aviso.append(contenido);
      const cerrar = document.createElement('button');
      cerrar.type = 'button';
      cerrar.className = 'aviso-cerrar';
      cerrar.setAttribute('aria-label', 'Cerrar');
      cerrar.textContent = '×';
      cerrar.addEventListener('click', () => aviso.remove());
      aviso.appendChild(cerrar);
      contenedor.appendChild(aviso);
      if (duracion) setTimeout(() => aviso.remove(), duracion);
    },

    /**
     * Sonido de aviso generado con Web Audio (sin archivos).
     * Los navegadores solo permiten sonar después de que el usuario interactuó con la página.
     */
    sonar(tipo = 'aviso') {
      const TONOS = {
        aviso: [[880, 0.15], [1320, 0.2]],
        alerta: [[660, 0.2], [440, 0.2], [660, 0.3]],
        ingreso: [[1046, 0.12]],
      };
      try {
        const Contexto = window.AudioContext || window.webkitAudioContext;
        if (!Contexto) return;
        PFC._audio ??= new Contexto();
        const ctx = PFC._audio;
        let inicio = ctx.currentTime;
        (TONOS[tipo] ?? TONOS.aviso).forEach(([frecuencia, duracion]) => {
          const oscilador = ctx.createOscillator();
          const volumen = ctx.createGain();
          oscilador.frequency.value = frecuencia;
          volumen.gain.setValueAtTime(0.2, inicio);
          volumen.gain.exponentialRampToValueAtTime(0.001, inicio + duracion);
          oscilador.connect(volumen).connect(ctx.destination);
          oscilador.start(inicio);
          oscilador.stop(inicio + duracion);
          inicio += duracion + 0.05;
        });
      } catch (error) {
        console.warn('No se pudo reproducir el sonido:', error);
      }
    },
  });

  // Buscadores
  const RUTAS_BUSQUEDA = { usuarios: '/usuarios/buscar', fichajes: '/fichajes/buscar', clases: '/clases/buscar' };
  const NOMBRE_O_DNI = /^(\d{7,8}|[\p{L}\s'.-]+)$/u;

  document.querySelectorAll('[data-buscar]').forEach((input) => {
    input.addEventListener('keydown', (evento) => {
      if (evento.key !== 'Enter') return;
      const tipo = input.dataset.buscar;
      const termino = input.value.trim();
      if (tipo !== 'clases' && !NOMBRE_O_DNI.test(termino)) {
        alert('Ingresá un nombre o un DNI de 7 u 8 dígitos.');
        return;
      }
      location.href = PFC.url(RUTAS_BUSQUEDA[tipo]) + '?q=' + encodeURIComponent(termino);
    });
  });

  // Confirmación antes de enviar formularios destructivos
  document.addEventListener('submit', (evento) => {
    const mensaje = evento.target.dataset?.confirmar;
    if (mensaje && !confirm(mensaje)) evento.preventDefault();
  });

  // Volver: usa el historial si venimos de la app, si no la ruta indicada
  document.querySelectorAll('[data-volver]').forEach((boton) => {
    boton.addEventListener('click', () => {
      if (document.referrer.startsWith(location.origin) && history.length > 1) history.back();
      else location.href = boton.dataset.volver;
    });
  });

  // Mensajes flash
  document.querySelectorAll('[data-cerrar-flash]').forEach((boton) => {
    boton.addEventListener('click', () => boton.closest('.flash')?.remove());
  });

  // Acciones rápidas: abren su ventana (<dialog id="dialogo-ACCION">)
  const ACCIONES = {
    'fichaje-manual': 'dialogo-fichaje-manual',
    'pago-manual': 'dialogo-pago-manual',
  };

  document.querySelectorAll('dialog.dialogo').forEach((dialogo) => {
    dialogo.querySelectorAll('[data-cerrar-dialogo]').forEach((b) => b.addEventListener('click', () => dialogo.close()));
    // Click en el fondo oscuro: cerrar
    dialogo.addEventListener('click', (evento) => {
      if (evento.target === dialogo) dialogo.close();
    });
  });

  // Filtro de opciones en selects múltiples largos: <input data-filtrar-select="idDelSelect">
  document.querySelectorAll('[data-filtrar-select]').forEach((input) => {
    const select = document.getElementById(input.dataset.filtrarSelect);
    if (!select) return;
    input.addEventListener('input', () => {
      const texto = input.value.trim().toLowerCase();
      Array.from(select.options).forEach((op) => {
        op.hidden = texto !== '' && !op.textContent.toLowerCase().includes(texto) && !op.selected;
      });
    });
  });

  // Formulario de pago: el monto sigue al plan elegido (cuota × meses pagos − descuento).
  document.querySelectorAll('[data-form-pago]').forEach((form) => {
    const cuota = parseFloat(form.dataset.cuota) || 0;
    const plan = form.querySelector('select[name="plan"]');
    const monto = form.querySelector('input[name="monto"]');
    const detalle = form.querySelector('[data-detalle-plan]');
    if (!plan || !monto) return;
    const actualizar = () => {
      const op = plan.selectedOptions[0];
      const pagos = parseInt(op.dataset.pagos, 10) || 1;
      const bonificados = parseInt(op.dataset.bonificados, 10) || 0;
      const descuento = parseFloat(op.dataset.descuento) || 0;
      monto.value = (Math.round(cuota * pagos * (1 - descuento / 100) * 100) / 100).toString();
      if (detalle) {
        const total = pagos + bonificados;
        detalle.textContent = `Cubre ${total} ${total === 1 ? 'mes' : 'meses'}` + (bonificados ? ` (${bonificados} bonificado${bonificados > 1 ? 's' : ''})` : '');
      }
    };
    plan.addEventListener('change', actualizar);
    actualizar();
  });

  document.querySelectorAll('[data-accion]').forEach((boton) => {
    boton.addEventListener('click', () => {
      const dialogo = document.getElementById(ACCIONES[boton.dataset.accion]);
      if (!dialogo) return;
      dialogo.querySelector('form')?.reset();
      dialogo.querySelectorAll('[data-volver-actual]').forEach((input) => (input.value = PFC.rutaActual()));
      dialogo.showModal();
    });
  });
})();
