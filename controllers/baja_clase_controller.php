<?php
session_start();

require_once '../models/clase_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$clase = new Clase();
$id_clase = isset($_GET['id_class']) ? $_GET['id_class'] : '';

if ($clase->deleteClaseById($id_clase)) {
  echo "<script>alert('Clase eliminada exitosamente'); window.location.href = '../controllers/lista_clases_controller.php';</script>";
} else {
  echo "<script>alert('Ocurrió un error al intentar eliminar la clase'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($id_clase) . "';</script>";
}
?>
