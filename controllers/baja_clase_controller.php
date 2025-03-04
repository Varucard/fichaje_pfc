<?php
session_start();

require_once '../models/clase_model.php';
require_once '../models/clase_alumno_model.php';
require_once '../models/clase_profesor_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$clase = new Clase();
$alumnoClase = new ClaseAlumno();
$profesorClase = new ClaseProfesor();

$id_clase = isset($_GET['id_class']) ? $_GET['id_class'] : '';
$eliminar = isset($_GET['eliminar']) ? $_GET['eliminar'] : 0;

// Si el usuario selecciono que quiere eliminar las matriculaciones para borrar la clase entro por aca
if ($eliminar) {
  $clase->deleteClaseById($id_clase, true);
  echo "<script>alert('Clase eliminada exitosamente'); window.location.href = '../controllers/lista_clases_controller.php';</script>";
}

// Verificar si hay matriculaciones activas
$hayMatriculaciones = $alumnoClase->getClaseAlumnoByIdClass($id_clase) || 
                      $profesorClase->getClaseProfesorByIdClass($id_clase);

if ($hayMatriculaciones && !$eliminar) {
  // Si hay matriculaciones y el usuario aún no confirmó, preguntar si desea eliminarlas
  echo "<script>
    if (confirm('No es posible eliminar la clase porque tiene matriculaciones activas. ¿Deseas eliminarlas?')) {
      window.location.href = 'baja_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "&eliminar=1';
    } else {
      window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';
    }
  </script>";
  exit;
}

if ($clase->deleteClaseById($id_clase)) {
  echo "<script>alert('Clase eliminada exitosamente'); window.location.href = '../controllers/lista_clases_controller.php';</script>";
} else {
  echo "<script>alert('Ocurrió un error al intentar eliminar la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
}
?>
