<?php

require_once '../models/user_model.php';
require_once 'pagos_controller.php';

$user = new User();

global $user;

// Valido que lo indispensable me llegue
if (empty($_POST['rfid']) || empty($_POST['dni']) || empty($_POST['name'])) {
  echo "<script>alert('Faltan datos N° de Llavero, N° de Documento, Nombre son campos obligatorios'); window.location.href = '../views/cargar_usuario_views.php';</script>";
  exit; 
} 

// Si el Usuario no existe lo guardo
if ($user->getUserByDNI($_POST['dni'])) {
  echo "<script>alert('El usuario ya se encuentra registrado'); window.location.href = '../views/cargar_usuario_views.php';</script>";
  exit; 
}

// Si todo va bien guardo al Usuario
$nuevoUsuario = [
  $rfid = $_POST['rfid'],
  $dni = $_POST['dni'],
  $nombre = $_POST['name'],
  $apellido = $_POST['surname'],
  $birth_day = $_POST['birth_day'],
  $email = $_POST['email'],
  $telefono = $_POST['phone'],
];

if ($user->cargarUsuario($nuevoUsuario)) {

  $usuario = $user->getUserByDNI($_POST['dni']);

  if (nuevo_pago($usuario[0]['id_user'])) {
    echo "<script>alert('Usuario cargado exitosamente'); window.location.href = '../index.php';</script>";
    exit; 
  } else {
    echo "<script>alert('Ocurrio un error con la fecha de pago'); window.location.href = '../index.php';</script>";
  }
} else {
  echo "<script>alert('Ocurrio un error, por favor voler a cargar el usuario'); window.location.href = '../index.php';</script>";
}

?>