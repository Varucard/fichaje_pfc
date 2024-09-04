<?php
session_start();

require_once '../models/clase_model.php';
require_once '../helpers/url_helper.php';

$clase = new Clase();

$id_class = isset($_GET['id_class']) ? $_GET['id_class'] : '';

if (empty($id_class)) {
  redirect('views/busqueda_clases_view.php'); // Redirige si no hay un Id de Clase
  exit;
}

$clase_data = $clase->getClaseById($id_class);
if (!$clase_data) {
redirect('views/busqueda_clases_view.php'); // Redirige si no se encuentra el usuario
exit;
}

include '../views/clase_view.php';
