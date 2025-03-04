<?php
session_start();

require_once '../models/clase_alumno_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$alumnoClase = new ClaseAlumno();
$id_alumno = isset($_GET['id_alumno']) ? $_GET['id_alumno'] : '';
$id_clase = isset($_GET['id_clase']) ? $_GET['id_clase'] : '';

if ($alumnoClase->deleteClaseAlumnoByIds($id_alumno, $id_clase)) {
  echo "<script>alert('Alumno eliminado exitosamente de la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
} else {
  echo "<script>alert('Ocurrió un error al intentar eliminar al Alumno de la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
}
?>
