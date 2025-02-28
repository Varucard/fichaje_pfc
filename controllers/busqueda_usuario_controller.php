<?php
session_start();

require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

if (isset($_GET['busqueda']) && isset($_GET['tipo_busqueda'])) {
  $busqueda = trim($_GET['busqueda']);
  $tipoBusqueda = trim($_GET['tipo_busqueda']);

  $userModel = new User();
  $resultadosBusqueda = [];

  switch ($tipoBusqueda) {
    case 'dni':
      array_push($resultadosBusqueda, $userModel->getUserByDni($busqueda));
      break;
    case 'name':
      array_push($resultadosBusqueda, $userModel->getUserByName($busqueda));
      break;
    default:
      redirect('../views/dashboard_view.php');
      exit;
  }

  if (empty($resultadosBusqueda)) {
    $_SESSION['resultados_busqueda'] = []; 
  } else {
    $_SESSION['resultados_busqueda'] = $resultadosBusqueda;
  }

  header('Location: ../views/busqueda_usuario_view.php');
  exit;
} else {
  redirect('views/dashboard_view.php'); 
  exit;
}
