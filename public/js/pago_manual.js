document.getElementById('cargar_pago_manual').addEventListener('click', function() {
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
        var id_user = document.getElementById('id').value;
        window.location.href = '../controllers/nuevo_pago_manual_controller.php?id_user=' + encodeURIComponent(id_user) + '&fecha_pago_manual=' + encodeURIComponent(fecha_pago_manual_sql);
      } else {
        alert('Formato de fecha incorrecto. Por favor, use DD-MM-YYYY.');
      }
    } else {
      alert('Formato de fecha incorrecto. Por favor, use DD-MM-YYYY.');
    }
  }
});
