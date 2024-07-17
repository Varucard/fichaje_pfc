<?php
session_start();
require_once '../models/user_model.php';

$user = new User();

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

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
  echo "<script>alert('Usuario desactivado exitosamente'); window.location.href = '../views/busqueda_usuario_view.php';</script>";
} else {
  echo "<script>alert('Ocurrió un error al desactivar el usuario'); window.location.href = '../views/usuario_view.php?dni=$dni';</script>";
}
?>
