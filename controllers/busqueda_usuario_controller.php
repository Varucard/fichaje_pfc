<?php
require_once '../models/user_model.php';

if (isset($_GET['busqueda']) && isset($_GET['tipo_busqueda'])) {
  $busqueda = $_GET['busqueda'];
  $tipoBusqueda = $_GET['tipo_busqueda'];

  $userModel = new User();
  $resultados = [];

  switch ($tipoBusqueda) {
    case 'dni':
      $resultados = $userModel->getUserByDni($busqueda);
      break;
    case 'rfid':
      $resultados = $userModel->getUserByRfid($busqueda);
      break;
    case 'name':
      $resultados = $userModel->getUserByName($busqueda);
      break;
    default:
      echo "<script>alert('Ocurrio un error, por favor, probar nuevamente'); window.location.href = '../index.php';</script>";
      exit; 
  }

  // Verifica si se encontraron resultados
  if (empty($resultados)) {
    echo "<script>alert('No se encontraron resultados'); window.location.href = '../index.php';</script>";
    exit;
  } else {
    // Redirige a la vista de resultados
    header('Location: ../views/busqueda_usuario_view.php?resultados=' . urlencode(json_encode($resultados)));
    exit;
  }
} else {
  echo "<script>alert('Ocurrio un error, por favor, probar nuevamente'); window.location.href = '../index.php';</script>";
  exit; 
}
?>
