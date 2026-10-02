<?php
session_start();

require_once '../models/clase_model.php';
require_once '../models/clase_profesor_model.php';
require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

if (isset($_GET['busqueda'])) {
  $busqueda = trim($_GET['busqueda']);
  
  $claseModel = new Clase();
  $profesorModel = new ClaseProfesor ();
  $userModel = new User();
  $profesoresClase = [];
  $clases = [];
  $profesores = [];
  $profesoresPorClase = [];
  
  $clases = $claseModel->getClasesByNameClase($busqueda);

  if ($clases) {
    foreach ($clases as $clase) {
      $profesoresClase = $profesorModel->getClaseProfesorByIdClass($clase->id_class);
      foreach ($profesoresClase as $aux) {
        $profesor = $userModel->getUserByID($aux->id_user);
        $profesoresPorClase[$clase->id_class][] = $profesor;
      }
    }
  }

  if (empty($resultadosBusqueda)) {
    $_SESSION['resultados_busqueda'] = []; 
  } else {
    $_SESSION['resultados_busqueda'] = $resultadosBusqueda;
  }

  include '../views/busqueda_clases_view.php';
  exit;
}

include '../views/busqueda_clases_view.php';
exit;
