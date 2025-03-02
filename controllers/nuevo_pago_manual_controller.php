<?php
session_start();

require_once '../models/pago_model.php';
require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$user = new User();

// Establecer la zona horaria de Argentina
date_default_timezone_set('America/Argentina/Buenos_Aires');

function nuevo_pago_manual($id_user, $fecha_pago_manual) {
  $pago = new Pagos();

  // Convertir la fecha de pago manual a DateTime
  $fecha_pago_manual_dt = DateTime::createFromFormat('Y-m-d', $fecha_pago_manual);

  if ($fecha_pago_manual_dt === false) {
    return false; // Fecha inválida
  }

  // Calcular la fecha 30 días después
  $fecha_renovacion_dt = clone $fecha_pago_manual_dt;
  $fecha_renovacion_dt->modify('+30 days');

  // Si la fecha de pago es 31 y la fecha renovacion es en un mes que no tiene 31 días, ajustamos
  if ($fecha_renovacion_dt->format('d') == '31') {
    // Si es 31 de cualquier mes, ajustamos a 1° del siguiente mes
    $fecha_renovacion_dt->modify('first day of next month');
  }

  // Formatear las fechas en formato SQL
  $fecha_pago_manual_str = $fecha_pago_manual_dt->format('Y-m-d H:i:s');
  $fecha_renovacion_str = $fecha_renovacion_dt->format('Y-m-d H:i:s');

  $nuevo_pago = [
    $id_user,
    $fecha_pago_manual_str,
    $fecha_renovacion_str
  ];

  return $pago->cargarPago($nuevo_pago);
}

// Recolecto los datos que me llegan por URL
$id_user = isset($_GET['id_user']) ? $_GET['id_user'] : '';
$dni_user = isset($_GET['dni_user']) ? $_GET['dni_user'] : '';
$fecha_pago_manual = isset($_GET['fecha_pago_manual']) ? $_GET['fecha_pago_manual'] : '';

// Trato de conseguir el Usuario por ID
$usuario = $user->getUserByID($id_user);

// Si el usuario no aparece con id lo busco con el DNI, una seguridad para user el mismo controladora en diferentes casos
if (!$usuario) {
  $usuario = $user->getUserByDNI($dni_user);
  $id_user = $usuario['id_user'];
}

if (nuevo_pago_manual($id_user, $fecha_pago_manual)) {
  echo "<script>alert('Pago registrado exitosamente'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . urlencode($usuario['dni']) . "';</script>";
  exit;
} else {
  echo "<script>alert('Ocurrió un error al registrar el pago'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . urlencode($usuario['dni']) . "';</script>";
  exit;
}
?>
