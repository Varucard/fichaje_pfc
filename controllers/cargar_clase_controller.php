<?php
session_start();

require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$user = new User();

// Obtener solo los usuarios que son profesores
$profesores = $user->getUsersByRole(3);

include '../views/cargar_clase_view.php';
