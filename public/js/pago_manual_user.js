document.getElementById('cargar_pago_manual_usuario').addEventListener('click', function () {
  // Solicitar DNI
  var dni_user = prompt('Ingrese el DNI:');
  
  // Validar que el DNI sea un número válido con al menos 7 dígitos
  if (dni_user === null) {
    return;
  }

  console.log(dni_user);

  if (dni_user.trim() === '' || isNaN(dni_user) || dni_user.length < 7) {
    alert('DNI incorrecto. Por favor, ingrese un número válido con al menos 7 dígitos.');
    return;
  }

  // Solicitar Fecha
  var fecha_pago_manual = prompt('Ingrese la fecha (DD-MM-YYYY):');
  if (fecha_pago_manual === null) {
    return;
  }

  if (fecha_pago_manual.trim() === '') {
    alert('La fecha no puede estar vacía. Por favor, use el formato DD-MM-YYYY.');
    return;
  }

  // Validar formato de la fecha
  var fecha_parts = fecha_pago_manual.split('-');
  if (fecha_parts.length === 3) {
    var dia = parseInt(fecha_parts[0], 10);
    var mes = parseInt(fecha_parts[1], 10);
    var anio = parseInt(fecha_parts[2], 10);

    // Validar que los valores sean números y formen una fecha válida
    if (!isNaN(dia) && !isNaN(mes) && !isNaN(anio) && validarFecha(dia, mes, anio)) {
      // Convertir la fecha al formato SQL (YYYY-MM-DD)
      var fecha_pago_manual_sql = anio + '-' + mes.toString().padStart(2, '0') + '-' + dia.toString().padStart(2, '0');

      // Redirigir con DNI y Fecha al controlador
      window.location.href = '../controllers/nuevo_pago_manual_controller.php?dni_user=' + encodeURIComponent(dni_user) + '&fecha_pago_manual=' + encodeURIComponent(fecha_pago_manual_sql);
    } else {
      alert('Fecha inválida. Por favor, ingrese una fecha válida en el formato DD-MM-YYYY.');
    }
  } else {
    alert('Formato de fecha incorrecto. Por favor, use el formato DD-MM-YYYY.');
  }
});

// Función para validar la fecha
function validarFecha(dia, mes, anio) {
  // Crear un objeto Date con la fecha proporcionada
  var fecha = new Date(anio, mes - 1, dia);

  // Comprobar que los valores coincidan con la fecha real
  return (
    fecha.getFullYear() === anio &&
    fecha.getMonth() === mes - 1 &&
    fecha.getDate() === dia
  );
}
