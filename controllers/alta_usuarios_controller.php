<?php

require_once '../models/user_model.php';
require_once 'pagos_controller.php';

$user = new User();
$aux = null;

global $user;

// Valido que lo indispensable me llegue (N° de Llavero, N° de Documento y nombre)
if (empty($_POST['rfid']) || empty($_POST['dni']) || empty($_POST['name'])) {
  echo "<script>alert('Faltan datos N° de Llavero, N° de Documento, Nombre son campos obligatorios'); window.location.href = '../views/cargar_usuario_views.php';</script>";
  exit; 
} 

// Verifico si el Usuario existe, si existe corto la ejecución
$aux = $user->getUserByDNI($_POST['dni']);
if ($aux) {
  if ($aux[0]['asset'] == 1) {
    echo "<script>alert('El usuario ya se encuentra registrado'); window.location.href = '../views/usuario_view.php?dni=" . htmlspecialchars($_POST['dni']) . "';</script>";
    exit; 
  } else {
    echo "<script>alert('El usuario ya se encuentra registrado, pero se encuentra inactivo'); window.location.href = '../views/usuario_view.php?dni=" . htmlspecialchars($_POST['dni']) . "';</script>";
    exit; 
  }
}

// Verifico no duplicar llaveros, a menos que el usuario que lo tiene se encuentre desactivado
$aux = $user->getUserByRFID($_POST['rfid']);
if ($aux) {
  if ($aux[0]['asset'] == 1) {
    echo "<script>alert('El llavero ya se encuentra registrado en un Usuario activo'); window.location.href = '../views/usuario_view.php?dni=" . htmlspecialchars($aux[0]['dni']) . "';</script>";
    exit; 
  }
}

// Creo al nuevo usuario
$nuevoUsuario = [
  $rfid = $_POST['rfid'],
  $dni = $_POST['dni'],
  $nombre = $_POST['name'],
  $apellido = $_POST['surname'],
  $birth_day = $_POST['birth_day'],
  $email = $_POST['email'],
  $telefono = $_POST['phone'],
];

// Lo guardo en la BD, si todo fue bien lo busco y le creo su fecha de pago
if ($user->cargarUsuario($nuevoUsuario)) {

  $usuario = $user->getUserByDNI($_POST['dni']);

  if (nuevo_pago($usuario[0]['id_user'])) {
    echo "<script>alert('Usuario cargado exitosamente'); window.location.href = '../views/usuario_view.php?dni=" . htmlspecialchars($_POST['dni']) . "';</script>";
    exit; 
  } else {
    echo "<script>alert('Ocurrio un error con la fecha de pago'); window.location.href = '../index.php';</script>";
  }
} else {
  echo "<script>alert('Ocurrio un error, por favor voler a cargar el usuario'); window.location.href = '../index.php';</script>";
}

?>