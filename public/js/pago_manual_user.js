document.getElementById('cargar_pago_manual_usuario').addEventListener('click', function() {
  // Solicitar DNI
  var dni = prompt('Ingrese el DNI:');
  
  // Validar que el DNI sea un número y tenga una longitud mínima
  if (dni && !isNaN(dni) && dni.length >= 7) {
    // Solicitar Fecha
    var fecha_pago_manual = prompt('Ingrese la fecha (DD-MM-YYYY):');
    if (fecha_pago_manual) {
      var fecha_parts = fecha_pago_manual.split('-');
      if (fecha_parts.length === 3) {
        var dia = fecha_parts[0];
        var mes = fecha_parts[1];
        var anio = fecha_parts[2];

        // Validar que dia, mes y anio sean números
        if (!isNaN(dia) && !isNaN(mes) && !isNaN(anio)) {
          var fecha_pago_manual_sql = anio + '-' + mes + '-' + dia;
          
          // Redirigir con DNI y Fecha al controlador
          window.location.href = '../controllers/nuevo_pago_manual_controller.php?&dni=' + encodeURIComponent(dni) + '&fecha_pago_manual=' + encodeURIComponent(fecha_pago_manual_sql);
        } else {
          alert('Formato de fecha incorrecto. Por favor, use DD-MM-YYYY.');
        }
      } else {
        alert('Formato de fecha incorrecto. Por favor, use DD-MM-YYYY.');
      }
    }
  } else {
    alert('DNI incorrecto. Por favor, ingrese un número válido con al menos 7 dígitos.');
  }
});
