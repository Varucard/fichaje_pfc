<?php
session_start();

require_once '../models/clase_alumno_model.php';
require_once '../models/clase_profesor_model.php';
require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$userClase = new User();
$alumnoClase = new ClaseAlumno();
$profesorClase = new ClaseProfesor();

$dni_user = isset($_GET['dni_user']) ? $_GET['dni_user'] : '';
$id_clase = isset($_GET['id_clase']) ? $_GET['id_clase'] : '';

$user = $userClase->getUserByDNI($dni_user);

// Verifico que el Usuario exista
if (!$user) {
  echo "<script>alert('El alumno/ profesor no se encuentra registrado'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
} else if ($alumnoClase->getClaseAlumnoByIdAlumnoAndIdClass($user['id_user'], $id_clase) || $profesorClase->getClaseProfesorByIdProfesorAndIdClass($user['id_user'], $id_clase)) {
  // Verifico que el Usuario ya no este agregado en la clase tanto como profesor o alumno
  echo "<script>alert('El Profesor o Alumno ya se encuentra registrado en la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
  exit;
}

// Agrego los profesores
if ($user['type_user'] == 1) {
  if ($profesorClase->createClaseProfesor($user['id_user'], $id_clase)) {
    echo "<script>alert('Profesor agregado exitosamente de la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
  } else {
    echo "<script>alert('Hubo un inconveniente al agregar al profesor a la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
  }
}

// Agrego los alumnos
if ($user['type_user'] == 2) {
  if ($alumnoClase->createClaseAlumno($user['id_user'], $id_clase)) {
    echo "<script>alert('Alumno agregado exitosamente de la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
  } else {
    echo "<script>alert('Hubo un inconveniente al agregar al alumno a la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
  }
}

?>
