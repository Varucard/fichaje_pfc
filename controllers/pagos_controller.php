<?php

require_once '../models/pago_model.php';

function nuevo_pago($id_user) {
  $pago = new Pagos();

  // Datos para cargar la tabla de pagos
  // Obtener la fecha actual en formato SQL
  $fecha_actual = new DateTime();
  $fecha_actual_str = $fecha_actual->format('Y-m-d H:i:s');
  
  // Calcular la fecha 30 días después en formato SQL
  $fecha_30_dias_mas = new DateTime();
  $fecha_30_dias_mas->modify('+30 days');
  $fecha_30_dias_mas_str = $fecha_30_dias_mas->format('Y-m-d H:i:s');
  
  $nuevo_pago = [
    $id_user,
    $fecha_actual_str,
    $fecha_30_dias_mas_str
  ];

  if ($pago->cargarPago($nuevo_pago)) 
    return true;
  else 
    return false;

}

?>