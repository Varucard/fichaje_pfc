<?php
session_start();

require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$user = new User();

$usuarios = $user->getUsers();
$resultados = [];

// Traigo unicamente los usuarios Profesores
foreach ($usuarios as $usuario) {
  if ($usuario['type_user'] == 3)
  $resultados[] = $usuario;
}

include '../views/lista_profesores_view.php';

?>
