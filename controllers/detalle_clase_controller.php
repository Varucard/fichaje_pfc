<?php
session_start();

require_once '../helpers/url_helper.php';
require_once '../models/clase_model.php';
require_once '../models/clase_profesor_model.php';
require_once '../models/clase_alumno_model.php';

checkSesion();

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
redirect('views/busqueda_clases_view.php'); // Redirige si no se encuentra el usuario
exit;
}

$profesores = $claseProfe->getClaseProfesorByIdClass($id_class);

var_dump($profesores);
exit;
// $clientes = $user->getUsersByRole(2); //TODO El cliente tiene que estar inscripto en la clase

include '../views/clase_view.php';
