<?php

session_start();
require_once '../models/pago_model.php';
require_once '../models/fichaje_model.php';
require_once '../models/user_model.php';

$user = new User();
$pagos = new Pagos();
$fichaje = new Fichajes();

// Captura el DNI del usuario de la URL
$dni_user = isset($_GET['dni_user']) ? $_GET['dni_user'] : '';

// Traer el usuario por DNI para validar que exista
$usuario = $user->getUserByDNI($dni_user);

// Si el usuario no existe
if (empty($usuario[0])) {
  echo "<script>alert('Usuario inexistente'); window.location.href = '../views/fichajes_view.php';</script>";
  exit();
}

// Obtener el ID del usuario
$id_user = $usuario[0]['id_user'];

// Obtener el pago mas actual del Usuario
$pago = $pagos->getPagoActualByUser($id_user);

// Si el usuario no tiene pagos registrados
if (empty($pago)) {
  echo "<script>alert('El usuario no tiene pagos registrados. Debe abonar antes de fichar.'); window.location.href = '../views/fichajes_view.php';</script>";
  exit();
}

// Verificar si la fecha de pago está vencida
date_default_timezone_set('America/Argentina/Buenos_Aires');
$fecha_actual = new DateTime();
$fecha_vencida = false;
$fecha_renovacion = new DateTime($pago['date_of_renovation']);
if ($fecha_actual > $fecha_renovacion) {
  $fecha_vencida = true;
}

// Si la fecha de pago está vencida
if ($fecha_vencida) {
  echo "<script>alert('La fecha de pago está vencida. Debe abonar antes de fichar.'); window.location.href = '../views/fichajes_view.php';</script>";
  exit();
}

$fecha_fichada = $fecha_actual->format('Y-m-d H:i:s');

if ($fichaje->guardarFichada($id_user, $fecha_fichada)) {
  echo "<script>alert('Fichada registrada exitosamente.'); window.location.href = '../views/fichajes_view.php';</script>";
  exit();
} else {
  echo "<script>alert('Ocurrio un inconveniente al momento de registrar la fichada del usuario.'); window.location.href = '../views/fichajes_view.php';</script>";
  exit();
}
?>
