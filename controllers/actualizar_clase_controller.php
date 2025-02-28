<?php
session_start();

require_once '../models/clase_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$clase = new Clase();

// Captura los datos del formulario
$id_clase = $_POST['id'];
$nombre_clase = $_POST['name_class'];
$precio_clase = $_POST['precio'];

$data = [
  'id_class' => $id_clase,
  'name_class' => $nombre_clase,
  'price_class' => $precio_clase,
];

// Actualiza la clase
if ($clase->updateClase($data)) {
  echo "<script>alert('Clase actualizada exitosamente'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
} else {
  echo "<script>alert('Ocurrió un error al intentar actualizar la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
}

?>
