<?php
session_start();
require_once '../models/user_model.php';

$user = new User();

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

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
  echo "<script>alert('Usuario reactivado exitosamente'); window.location.href = '../views/busqueda_usuario_view.php';</script>";
} else {
  echo "<script>alert('Ocurrió un error al reactivar el usuario'); window.location.href = '../views/usuario_view.php?dni=$dni';</script>";
}
?>
