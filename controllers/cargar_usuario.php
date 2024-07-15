<?php

require_once '../models/user_model.php';

$user = new User();

// Obtener la fecha actual en formato SQL
$fecha_actual = new DateTime();
$fecha_actual_str = $fecha_actual->format('Y-m-d H:i:s');

// Calcular la fecha 30 días después en formato SQL
$fecha_30_dias_mas = new DateTime();
$fecha_30_dias_mas->modify('+30 days');
$fecha_30_dias_mas_str = $fecha_30_dias_mas->format('Y-m-d H:i:s');

$fechas = [
  $fecha_alta = $fecha_actual_str,
  $fecha_renovacion = $fecha_30_dias_mas_str
];

$nuevoUsuario = [
  $rfid = $_POST['rfid'],
  $dni = $_POST['dni'],
  $nombre = $_POST['name'],
  $apellido = $_POST['surname'],
  $email = $_POST['email'],
  $telefono = $_POST['phone'],
];

if ($user->cargarUsuario($nuevoUsuario)) {
  echo "<script>alert('Usuario cargado exitosamente'); window.location.href = '../index.php';</script>";
  exit; 
} else {
  echo "<script>alert('Ocurrio un error, por favor voler a cargar el usuario'); window.location.href = '../index.php';</script>";
}

?>