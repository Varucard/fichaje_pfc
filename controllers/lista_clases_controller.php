<?php
session_start();

require_once '../models/clase_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$clase = new Clase();

$clases = $clase->getClases();

include '../views/lista_clases_view.php';

?>
