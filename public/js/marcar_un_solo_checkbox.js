// Obtén los dos checkboxes
const pagoCheckbox = document.getElementById('pago');
const profesorCheckbox = document.getElementById('profesor');

// Función para asegurar que solo uno esté marcado
function ensureSingleCheckbox() {
  if (pagoCheckbox.checked && profesorCheckbox.checked) {
    // Si ambos están marcados, desmarcar el segundo
    if (this === pagoCheckbox) {
      profesorCheckbox.checked = false;
    } else {
      pagoCheckbox.checked = false;
    }
  }
}

// Asignar la función a ambos checkboxes
pagoCheckbox.addEventListener('change', ensureSingleCheckbox);
profesorCheckbox.addEventListener('change', ensureSingleCheckbox);
