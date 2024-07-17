<?php
require_once '../models/user_model.php';

$user = new User();

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

if ($user->activarUsuario($dni)) {
    echo "<script>alert('Usuario reactivado exitosamente'); window.location.href = '../views/usuario_view.php?dni=$dni';</script>";
} else {
    echo "<script>alert('Ocurrió un error al reactivar el usuario'); window.location.href = '../views/usuario_view.php?dni=$dni';</script>";
}
?>
