<?php
session_start();

require_once '../helpers/url_helper.php';
require_once '../models/clase_model.php';
require_once '../models/clase_profesor_model.php';
require_once '../models/clase_alumno_model.php';
require_once '../models/user_model.php';

checkSesion();

$user = new User();
$clase = new Clase();
$claseProfe = new ClaseProfesor();
$claseAlumno = new ClaseAlumno();

$id_class = isset($_GET['id_class']) ? $_GET['id_class'] : '';

if (empty($id_class)) {
  redirect('views/busqueda_clases_view.php'); // Redirige si no hay un Id de Clase
  exit;
}

$clase_data = $clase->getClaseById($id_class);
if (!$clase_data) {
  redirect('views/busqueda_clases_view.php'); // Redirige si no se encuentra información de la clase
  exit;
}

$profesores_por_clase = $claseProfe->getClaseProfesorByIdClass($id_class);
$alumnos_por_clase = $claseAlumno->getClaseAlumnoByIdClass($id_class);
$profesores = [];
$clientes = [];

if (!empty($profesores_por_clase)) {
  foreach ($profesores_por_clase as $profesor_por_clase) {
    array_push($profesores, $user->getUserByID($profesor_por_clase->id_user));
  }
}

if (!empty($alumnos_por_clase)) {
  foreach ($alumnos_por_clase as $alumno_por_clase) {
    array_push($clientes, $user->getUserByID($alumno_por_clase->id_user));
  }
}

include '../views/clase_view.php';
