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

    /**
     * Ejecuta una consulta periódica (polling) que:
     * - se pausa mientras la pestaña está oculta y se reanuda al volver,
     * - no superpone pedidos (espera a que termine el anterior),
     * - ante errores espera cada vez más (hasta 1 minuto) para no saturar.
     * @param {() => Promise<any>} tarea
     */
    sondear(tarea, intervaloMs) {
      let espera = intervaloMs;
      let timer = null;
      const ciclo = () => {
        timer = null;
        if (document.hidden) return;
        tarea()
          .then(() => (espera = intervaloMs))
          .catch((error) => {
            espera = Math.min(espera * 2, 60000);
            console.warn('Consulta periódica fallida, reintento en', espera / 1000, 's:', error);
          })
          .finally(() => (timer = setTimeout(ciclo, espera)));
      };
      document.addEventListener('visibilitychange', () => {
        if (!document.hidden && timer === null) ciclo();
      });
      ciclo();
    },

    /** Copia texto al portapapeles; funciona también entrando por http://IP (sin HTTPS). */
    copiar(texto) {
      if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(texto);
      }
      return new Promise((resolver, rechazar) => {
        const campo = Object.assign(document.createElement('textarea'), { value: texto });
        campo.setAttribute('readonly', '');
        campo.style.cssText = 'position:fixed;opacity:0';
        document.body.appendChild(campo);
        campo.select();
        const ok = document.execCommand('copy');
        campo.remove();
        ok ? resolver() : rechazar(new Error('No se pudo copiar'));
      });
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
        // Si se creó antes de que el usuario tocara la página queda suspendido: reanudarlo.
        if (ctx.state === 'suspended') ctx.resume();
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

  // El audio se habilita con la primera interacción (política de los navegadores).
  const habilitarAudio = () => {
    const Contexto = window.AudioContext || window.webkitAudioContext;
    if (Contexto) {
      PFC._audio ??= new Contexto();
      if (PFC._audio.state === 'suspended') PFC._audio.resume();
    }
  };
  document.addEventListener('pointerdown', habilitarAudio, { once: true });
  document.addEventListener('keydown', habilitarAudio, { once: true });

  // Buscadores
  const RUTAS_BUSQUEDA = { usuarios: '/usuarios/buscar', fichajes: '/fichajes/buscar', clases: '/clases/buscar' };
  const NOMBRE_O_DNI = /^(\d{7,8}|[\p{L}\s'.-]+)$/u;
  // Los ingresos también se buscan por llavero (hex) o DNI parcial.
  const BUSQUEDA_FICHAJES = /^(\d{3,8}|[0-9A-Fa-f]{4,20}|[\p{L}\s'.-]+)$/u;

  document.querySelectorAll('[data-buscar]').forEach((input) => {
    input.addEventListener('keydown', (evento) => {
      if (evento.key !== 'Enter') return;
      const tipo = input.dataset.buscar;
      const termino = input.value.trim();
      const valido = tipo === 'clases' || (tipo === 'fichajes' ? BUSQUEDA_FICHAJES : NOMBRE_O_DNI).test(termino);
      if (!valido) {
        alert(tipo === 'fichajes' ? 'Ingresá un nombre, un DNI o un número de llavero.' : 'Ingresá un nombre o un DNI de 7 u 8 dígitos.');
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
    // Click en el fondo oscuro (fuera del recuadro): cerrar. Se miran las coordenadas
    // para no cerrar al hacer clic en el borde interno ni al soltar una selección afuera.
    dialogo.addEventListener('pointerdown', (evento) => {
      const r = dialogo.getBoundingClientRect();
      dialogo.dataset.clicAfuera = evento.target === dialogo
        && (evento.clientX < r.left || evento.clientX > r.right || evento.clientY < r.top || evento.clientY > r.bottom) ? '1' : '';
    });
    dialogo.addEventListener('click', (evento) => {
      if (evento.target === dialogo && dialogo.dataset.clicAfuera === '1') dialogo.close();
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
      // Fecha de hoy al abrir (no la del momento en que se cargó la página).
      const hoy = new Date(Date.now() - new Date().getTimezoneOffset() * 60000).toISOString().slice(0, 10);
      dialogo.querySelectorAll('input[type="date"][data-hoy]').forEach((input) => {
        input.value = hoy;
        input.max = hoy;
      });
      dialogo.showModal();
    });
  });
})();
