<?php
session_start();

require_once '../models/clase_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

if (isset($_GET['busqueda'])) {
  $busqueda = trim($_GET['busqueda']);
  
  $claseModel = new Clase();
  $clases = [];
  
  $clases = $claseModel->getClasesByNameClase($busqueda);
  
  if (empty($resultadosBusqueda)) {
    $_SESSION['resultados_busqueda'] = []; 
  } else {
    $_SESSION['resultados_busqueda'] = $resultadosBusqueda;
  }

  include '../views/busqueda_clases_view.php';
  exit;
}
