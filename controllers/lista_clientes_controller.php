<?php
session_start();

require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$user = new User();

$usuarios = $user->getUsers();
$resultados = [];

// Traigo unicamente los usuarios clientes
foreach ($usuarios as $usuario) {
  if ($usuario['type_user'] == 2)
  $resultados[] = $usuario;
}

include '../views/lista_clientes_view.php';

?>
