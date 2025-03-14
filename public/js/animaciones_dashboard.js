const hamIcons = document.querySelectorAll('.hamburger-icon');
const headerButtonGroup = document.querySelector('.header-button-group');
const mq = window.matchMedia('(min-width: 904px)');

function handleScreenChange(e) {
  hamIcons.forEach((hamIcon) => {
    if (e.matches) {
      // Pantalla grande (>= 904px)
      if (!hamIcon.classList.contains('hidden')) {
        hamIcon.classList.add('hidden'); // Ocultar hamburguesa primero
        headerButtonGroup.classList.remove('d-none'); // Mostrar botones

        setTimeout(() => {
          hamIcon.classList.add('d-none'); // Después de animación, ocultar completamente
          headerButtonGroup.classList.remove('hidden');
        }, 300);
      }
    } else {
      // Pantalla pequeña (< 904px)
      if (!headerButtonGroup.classList.contains('hidden')) {
        headerButtonGroup.classList.add('hidden'); // Aplicar animación a los botones

        setTimeout(() => {
          headerButtonGroup.classList.add('d-none'); // Ocultar botones con display: none
          hamIcon.classList.remove('d-none'); // Ahora sí, mostrar hamburguesa
          
          setTimeout(() => {
            hamIcon.classList.remove('hidden'); // Animación para mostrarlo
          }, 10);
        }, 300);
      }
    }
  });
}

// Agregar listener para detectar cambios en el tamaño de pantalla
mq.addEventListener('change', handleScreenChange);

// Ejecutar la función al cargar la página
handleScreenChange(mq);
