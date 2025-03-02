<?php
session_start();

require_once '../models/pago_model.php';
require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$user = new User();

// Establecer la zona horaria de Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

function nuevo_pago($id_user) {
  $pago = new Pagos();

  // Obtener la fecha actual en formato SQL
  $fecha_actual = new DateTime();
  $fecha_actual_str = $fecha_actual->format('Y-m-d H:i:s');

  // Calcular la fecha 30 días después
  $fecha_renovacion = clone $fecha_actual;
  $fecha_renovacion->modify('+30 days');

  // Si la fecha de renovación cae en un mes con menos días (ej. 31 de enero)
  $dia_renovacion = (int)$fecha_renovacion->format('d');
  if ($dia_renovacion < (int)$fecha_actual->format('d')) {
    // Si el día de la renovación es antes del día actual (en el siguiente mes), mover a 1° del siguiente mes
    $fecha_renovacion->modify('first day of next month');
  }

  // Formatear las fechas en formato SQL
  $fecha_renovacion_str = $fecha_renovacion->format('Y-m-d H:i:s');

  // Crear un array con los datos del nuevo pago
  $nuevo_pago = [
    $id_user,
    $fecha_actual_str,
    $fecha_renovacion_str
  ];

  // Cargar el nuevo pago en la base de datos
  return $pago->cargarPago($nuevo_pago);
}

$id_user = isset($_GET['id_user']) ? $_GET['id_user'] : '';
$usuario = $user->getUserByID($id_user);

// Verificar si se proporcionó un ID de usuario y si se pudo registrar el pago
if ($id_user && $usuario && nuevo_pago($id_user)) {
  echo "<script>alert('Pago registrado exitosamente'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . urlencode($usuario['dni']) . "';</script>";
} else {
  echo "<script>alert('Ocurrió un error al registrar el pago'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . urlencode($usuario['dni']) . "';</script>";
}
?>
