<?php
session_start();

require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$user_class = new User();

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

// Traigo al usuario desactivado
$user_disabled = $user_class->getUserByDNI($dni);

// Obtengo si hay, al unico usuario activo que tenga el RFID del usuario desactivado
$user_by_rfid = $user_class->getUserByRFID($user_disabled['rfid']);

if ($user_by_rfid) {
  // Si hay otro usuario activo con el mismo RFID y el RFID no es 'SIN LLAVERO', resetear el RFID del usuario actual
  if ($user_by_rfid['asset'] == 1 && $user_by_rfid['rfid'] != 'SIN LLAVERO') {
    $user_class->desetearRFID($user_disabled['id_user']);
  }
}

// Activar el usuario
if ($user_class->activarUsuario($user_disabled['id_user'])) {
  // Actualizar los resultados de la sesión
  if (isset($_SESSION['resultados_busqueda'])) {
    foreach ($_SESSION['resultados_busqueda'] as &$usuario) {
      if ($usuario['dni'] == $dni) {
        $usuario['asset'] = 1;
        break;
      }
    }
  }
  echo "<script>alert('Usuario reactivado exitosamente'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=$dni';</script>";
} else {
  echo "<script>alert('Ocurrió un error al reactivar el usuario'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=$dni';</script>";
}

?>
