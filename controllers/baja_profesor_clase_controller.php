<?php
session_start();

require_once '../models/clase_profesor_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$profesorClase = new ClaseProfesor();
$id_profesor = isset($_GET['id_profesor']) ? $_GET['id_profesor'] : '';
$id_clase = isset($_GET['id_clase']) ? $_GET['id_clase'] : '';

if ($profesorClase->deleteClaseProfesorById($id_profesor, $id_clase)) {
  echo "<script>alert('Profesor eliminado exitosamente de la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
} else {
  echo "<script>alert('Ocurrió un error al intentar eliminar al profesor de la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
}
?>
