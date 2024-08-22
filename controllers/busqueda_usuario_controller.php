<?php
session_start();

require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

if (isset($_GET['busqueda']) && isset($_GET['tipo_busqueda'])) {
  $busqueda = $_GET['busqueda'];
  $tipoBusqueda = $_GET['tipo_busqueda'];

  $userModel = new User();
  $resultados = [];

  switch ($tipoBusqueda) {
    case 'dni':
      $resultados = $userModel->getUserByDni($busqueda);
      break;
    case 'name':
      $resultados = $userModel->getUserByName($busqueda);
      break;
    default:
      echo "<script>alert('Ocurrio un error, por favor, probar nuevamente'); window.location.href = '../views/dashboard_view.php';</script>";
      exit; 
  }

  // Verifica si se encontraron resultados
  if (empty($resultados)) {
    echo "<script>alert('No se encontraron resultados'); window.location.href = '../views/dashboard_view.php';</script>";
    exit;
  } else {
    // Almacena los resultados en la sesión y redirige a la vista de búsqueda
    $_SESSION['resultados_busqueda'] = $resultados;
    header('Location:' . URLROOT . '/views/busqueda_usuario_view.php');
    exit;
  }
} else {
  echo "<script>alert('Ocurrio un error, por favor, probar nuevamente'); window.location.href = '../views/dashboard_view.php';</script>";
  exit; 
}
?>
