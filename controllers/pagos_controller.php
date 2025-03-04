<?php
session_start();

require_once '../models/pago_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

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

  return $pago->cargarPago($nuevo_pago);
}

function eliminar_pago($id_payment, $dni) {
  $pago = new Pagos();

  if ($pago->deletePagoById($id_payment)) {
    echo "<script>alert('Pago eliminado exitosamente'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . urlencode($dni) . "';</script>";
    exit;
  }
}

// Si me llega un ID de pago es para eliminarlo
$id_payment = isset($_GET['id_pago']) ? $_GET['id_pago'] : FALSE;
$dni_user = isset($_GET['dni']) ? $_GET['dni'] : '';

if ($id_payment) {
  eliminar_pago($id_payment, $dni_user);
}

?>