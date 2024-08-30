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
    echo "<script>alert('El Cliente/ Profesor ya se encuentra registrado'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . htmlspecialchars($_POST['dni']) . "';</script>";
    exit; 
  } else {
    echo "<script>alert('El Cliente/ Profesor ya se encuentra registrado, pero se encuentra inactivo'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . htmlspecialchars($_POST['dni']) . "';</script>";
    exit; 
  }
}

// Verifico no duplicar llaveros, a menos que el usuario que lo tiene se encuentre desactivado
if ($_POST['rfid'] != 'SIN LLAVERO') {
  $aux = $user->getUserByRFID($_POST['rfid']);
  if ($aux) {
    if ($aux[0]['asset'] == 1) {
      echo "<script>alert('El llavero ya se encuentra registrado en un Cliente activo'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . htmlspecialchars($aux[0]['dni']) . "';</script>";
      exit; 
    }
  }

  // Normalizar el RFID solo si no es "SIN LLAVERO"
  $rfidNormalizado = strtolower($_POST['rfid']); // Convertir a minúsculas
  $rfidNormalizado = str_replace(' ', '', $rfidNormalizado); // Eliminar espacios
} 

$rfidNormalizado = $_POST['rfid'];
// Normalizo los nombres y apellido
$nombreSinProcesar = $_POST['name'];
$apellidoSinProcesar = isset($_POST['surname']) ? $_POST['surname'] : '';

// Convertir el nombre y el apellido a tener la primera letra en mayúscula y el resto en minúscula
$nombreNormalizado = ucwords(strtolower($nombreSinProcesar));
$apellidoNormalizado = ucwords(strtolower($apellidoSinProcesar));

$tipo_usuario = isset($_POST['profesor']) ? 3 : null; // Profesor

// Creo al nuevo usuario
$nuevoUsuario = [
  $rfid = $rfidNormalizado,
  $dni = $_POST['dni'],
  $nombre = $nombreNormalizado,
  $apellido = $apellidoNormalizado,
  $birth_day = $_POST['birth_day'],
  $email = $_POST['email'],
  $telefono = $_POST['phone'],
  $tipo_usuario = $tipo_usuario
];

// Lo guardo en la BD
if ($user->cargarUsuario($nuevoUsuario)) {
  
  $usuario = $user->getUserByDNI($_POST['dni']);
  
  if (isset($_POST['profesor'])) {
    echo "<script>alert('Profesor cargado exitosamente'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . htmlspecialchars($_POST['dni']) . "';</script>";
    exit; 
  } else if (isset($_POST['pago'])) {
    if (nuevo_pago($usuario[0]['id_user'])) {
      echo "<script>alert('Cliente cargado exitosamente con Pago'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . htmlspecialchars($_POST['dni']) . "';</script>";
      exit; 
    } else {
      echo "<script>alert('Ocurrio un error con la fecha de pago'); window.location.href = '../views/cargar_usuario_view.php';</script>";
    }
  } else {
    echo "<script>alert('Cliente cargado exitosamente sin Pago'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . htmlspecialchars($_POST['dni']) . "';</script>";
    exit;
  }
} else {
  echo "<script>alert('Ocurrio un error, por favor voler a cargar el Cliente/ Profesor'); window.location.href = '../views/cargar_usuario_view.php';</script>";
}

?>
