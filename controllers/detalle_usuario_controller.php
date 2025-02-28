<?php
session_start();

require_once '../models/user_model.php';
require_once '../models/pago_model.php';
require_once '../models/clase_alumno_model.php';
require_once '../models/clase_profesor_model.php';
require_once '../models/clase_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$user = new User();
$pagos = new Pagos();
$alumnoClase = new ClaseAlumno();
$profesorClase = new ClaseProfesor();
$claseClass = new Clase();

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

if (empty($dni)) {
  redirect('views/busqueda_usuario_view.php'); // Redirige si no hay DNI
  exit;
}

$usuario = $user->getUserByDNI($dni);

if (!$usuario) {
  redirect('views/busqueda_usuario_view.php'); // Redirige si no se encuentra el usuario
  exit;
}

// Obtengo las clases ya sea un profesor o alumno
$clases = [];

if ($usuario['type_user'] == 2) {
  $clases = $alumnoClase->getClaseAlumnoByIdAlumno($usuario['id_user']);
  $tipo_usuario = 'id_alumno';
  $controlador = 'baja_alumno_clase_controller.php';
} else { 
  $clases = $profesorClase->getClaseProfesorByIdProfesor($usuario['id_user']);
  $tipo_usuario = 'id_profesor';
  $controlador = 'baja_profesor_clase_controller.php';
}

// Una vez que se si el usuario esta en una clase busco la información de esa clase
$dataClases = [];

foreach ($clases as $clase) {
  array_push($dataClases, $claseClass->getClaseById($clase->id_class));
}

$clases = $dataClases;

$pago = $pagos->getPagosByUser($usuario['id_user']);
$pago = array_slice($pago, 0, 5);
$pago = array_reverse($pago);

include '../views/usuario_view.php';
