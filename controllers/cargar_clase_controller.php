<?php
session_start();

require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$user = new User();

// Obtener solo los usuarios que son profesores
$profesores = $user->getUsersByRole(1);
$profesoresActivos = [];

foreach ($profesores as $profesor) {
  if ($profesor->asset == 1) {
    array_push($profesoresActivos, $profesor);
  }
}

include '../views/cargar_clase_view.php';
