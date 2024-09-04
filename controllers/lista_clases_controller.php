<?php
session_start();

require_once '../models/clase_model.php';

$clase = new Clase();

$clases = $clase->getClases();

include '../views/lista_clases_view.php';

?>
