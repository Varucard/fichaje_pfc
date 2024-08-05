<?php
require_once '../models/user_model.php';

$userModel = new User();
$date = date('Y-m-d'); // Obtener la fecha actual en formato YYYY-MM-DD
$usersWithBirthday = $userModel->getUsersFestejados($date);

echo json_encode($usersWithBirthday);
?>
