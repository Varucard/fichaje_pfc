<?php
session_start();

require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

date_default_timezone_set('America/Argentina/Buenos_Aires');

$userModel = new User();
$date = date('Y-m-d'); // Obtener la fecha actual en formato YYYY-MM-DD
$usersWithBirthday = $userModel->getUsersFestejados($date);

echo json_encode($usersWithBirthday);
?>
