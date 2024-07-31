<?php
session_start();
require_once '../models/user_model.php';

$user = new User();

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

// Verificar si el usuario con el DNI dado existe
$user_aux = $user->getUserByDNI($dni);

if ($user_aux) {
  // Obtener usuarios por el RFID del usuario actual
  $users_by_rfid = $user->getUserByRFID($user_aux[0]['rfid']);

  if ($users_by_rfid) {
    foreach ($users_by_rfid as $user_by_rfid) {
      // Si hay otro usuario activo con el mismo RFID y el RFID no es 'SIN LLAVERO', resetear el RFID del usuario actual
      if ($user_by_rfid['asset'] == 1 && $user_by_rfid['rfid'] != 'SIN LLAVERO') {
        $user->desetearRFID($user_aux[0]['id_user']);
        break;
      }
    }
  }

    // Activar el usuario
  if ($user->activarUsuario($dni)) {
    // Actualizar los resultados de la sesión
    if (isset($_SESSION['resultados_busqueda'])) {
      foreach ($_SESSION['resultados_busqueda'] as &$usuario) {
        if ($usuario['dni'] == $dni) {
          $usuario['asset'] = 1;
          break;
        }
      }
    }
    echo "<script>alert('Usuario reactivado exitosamente'); window.location.href = '../views/usuario_view.php?dni=$dni';</script>";
  } else {
    echo "<script>alert('Ocurrió un error al reactivar el usuario'); window.location.href = '../views/usuario_view.php?dni=$dni';</script>";
  }
} else {
  echo "<script>alert('Usuario no encontrado'); window.location.href = '../views/usuario_view.php';</script>";
}
?>
