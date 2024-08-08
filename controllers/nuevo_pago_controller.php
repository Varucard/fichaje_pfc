<?php
session_start();
require_once '../models/pago_model.php';
require_once '../models/user_model.php';

$user = new User();


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

$id_user = isset($_GET['id_user']) ? $_GET['id_user'] : '';

$usuario = $user->getUserByID($id_user);

if ($id_user && nuevo_pago($id_user)) {
  echo "<script>alert('Pago registrado exitosamente'); window.location.href = '../views/usuario_view.php?dni=" . urlencode($usuario[0]['dni']) . "';</script>";
} else {
  echo "<script>alert('Ocurrió un error al registrar el pago'); window.location.href = '../views/usuario_view.php?dni=" . urlencode($usuario[0]['dni']) . "';</script>";
}
?>
