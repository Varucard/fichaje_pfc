<?php
session_start();

require_once '../models/user_model.php';
require_once '../models/clase_alumno_model.php';
require_once '../models/clase_profesor_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$user = new User();
$alumnoClase = new ClaseAlumno;
$profesorClase = new ClaseProfesor;

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

$usuario = $user->getUserByDNI($dni);

// Si el usuario tiene matriculaciones activas no lo puedo desactivar
if ($alumnoClase->getClaseAlumnoByIdAlumno($usuario['id_user']) || $profesorClase->getClaseProfesorByIdProfesor($usuario['id_user'])) {
  echo "<script>alert('El profesor o alumno posee matriculaciones activas. Por favor eliminelas antes de desactivar al Usuario'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . htmlspecialchars($dni) . "';</script>";
  exit; 
}

if ($user->desactivarUsuario($dni)) {
  // Actualizar los resultados de la sesión
  if (isset($_SESSION['resultados_busqueda'])) {
    foreach ($_SESSION['resultados_busqueda'] as &$usuario) {
      if ($usuario['dni'] == $dni) {
        $usuario['asset'] = 0;
        break;
      }
    }
  }

  echo "<script>alert('Usuario desactivado exitosamente'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=$dni';</script>";
} else {
  echo "<script>alert('Ocurrió un error al desactivar el usuario'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=$dni';</script>";
}
?>
