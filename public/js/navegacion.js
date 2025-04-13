const hamIconInternal = document.querySelector('.hamburger-icon-internal');
const headerButtonGroup = document.querySelector('.header-button-group');
const mq = window.matchMedia('(min-width: 904px)');

const hamIcon = document.querySelector('.hamburger-icon-external');
const mobileMenu = document.querySelector('.mobile-menu');
const overlay = document.getElementById('overlay');

function handleScreenChange(e) {
    console.log(e, e.matches)
    if (e.matches) {
      // Pantalla grande (>= 904px)
      mobileMenu.classList.remove('show');
      overlay.classList.add('d-none');
      if (!hamIconInternal.classList.contains('hidden')) {
        hamIconInternal.classList.add('hidden'); // Ocultar hamburguesa primero
        headerButtonGroup.classList.remove('d-none'); // Mostrar botones
        
        setTimeout(() => {
          hamIconInternal.classList.add('d-none'); // Después de animación, ocultar completamente
          headerButtonGroup.classList.remove('hidden');
          
          
        }, 300);
      }
    } else {
      // Pantalla pequeña (< 904px)
      if (!headerButtonGroup.classList.contains('hidden')) {
        headerButtonGroup.classList.add('hidden'); // Aplicar animación a los botones

        setTimeout(() => {
          headerButtonGroup.classList.add('d-none'); // Ocultar botones con display: none
          hamIconInternal.classList.remove('d-none'); // Ahora sí, mostrar hamburguesa
          
          setTimeout(() => {
            hamIconInternal.classList.remove('hidden'); // Animación para mostrarlo
          }, 10);
        }, 300);
      }
    }
  ;
}

// Agregar listener para detectar cambios en el tamaño de pantalla
mq.addEventListener('change', handleScreenChange);

// Ejecutar la función al cargar la página
handleScreenChange(mq);

document.addEventListener('DOMContentLoaded', function () {


  hamIcon.addEventListener('click', () => {
    mobileMenu.classList.toggle('show');
    overlay.classList.toggle('d-none');
  });

  hamIconInternal.addEventListener('click', () => {
    mobileMenu.classList.toggle('show');
    overlay.classList.toggle('d-none');
  });

  overlay.addEventListener('click', () => {
    mobileMenu.classList.remove('show');
    overlay.classList.add('d-none');
  });
});
