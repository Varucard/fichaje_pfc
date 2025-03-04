document.getElementById('cargar_pago_manual').addEventListener('click', function() {
  var fecha_pago_manual = prompt('Ingrese la fecha (DD-MM-YYYY):');
  
  // Verificar si el usuario canceló el prompt
  if (fecha_pago_manual === null) {
    return;
  }

  // Validar que la fecha no esté vacía
  if (fecha_pago_manual.trim() === '') {
    alert('La fecha no puede estar vacía. Por favor, use el formato DD-MM-YYYY.');
    return;
  }

  // Dividir la fecha en partes
  var fecha_parts = fecha_pago_manual.split('-');
  if (fecha_parts.length === 3) {
    var dia = parseInt(fecha_parts[0], 10);
    var mes = parseInt(fecha_parts[1], 10);
    var anio = parseInt(fecha_parts[2], 10);

    // Validar que los valores sean números y que formen una fecha válida
    if (!isNaN(dia) && !isNaN(mes) && !isNaN(anio) && validarFecha(dia, mes, anio)) {
      // Convertir la fecha al formato SQL
      var fecha_pago_manual_sql = anio + '-' + (mes.toString().padStart(2, '0')) + '-' + (dia.toString().padStart(2, '0'));
      var id_user = document.getElementById('id').value;

      // Redirigir con los parámetros validados
      window.location.href = '../controllers/nuevo_pago_manual_controller.php?id_user=' + encodeURIComponent(id_user) + '&fecha_pago_manual=' + encodeURIComponent(fecha_pago_manual_sql);
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
