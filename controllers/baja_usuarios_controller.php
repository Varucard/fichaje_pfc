<?php
require_once '../models/user_model.php';

$user = new User();

$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

if ($user->desactivarUsuario($dni)) {
    echo "<script>alert('Usuario desactivado exitosamente'); window.location.href = '../views/usuario_view.php?dni=$dni';</script>";
} else {
    echo "<script>alert('Ocurrió un error al desactivar el usuario'); window.location.href = '../views/usuario_view.php?dni=$dni';</script>";
}
?>
