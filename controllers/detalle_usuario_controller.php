<?php
require_once '../models/user_model.php';
require_once '../models/pago_model.php';
require_once '../helpers/url_helper.php';

session_start();

$user = new User();
$pagos = new Pagos();

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

if (empty($dni)) {
    redirect('views/busqueda_usuario_view.php'); // Redirige si no hay DNI
    exit;
}

$usuario = $user->getUserByDNI($dni);
if (!$usuario) {
    redirect('views/busqueda_usuario_view.php'); // Redirige si no se encuentra el usuario
    exit;
}

$pago = $pagos->getPagosByUser($usuario[0]['id_user']);
$pago = array_slice($pago, 0, 5);
$pago = array_reverse($pago);

include '../views/usuario_view.php';
