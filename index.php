<?php
require_once 'config/config.php';
require_once 'helpers/url_helper.php';
require_once 'helpers/session_helper.php';

// Verificar si el usuario está autenticado
if (isset($_SESSION['user_id'])) {
    // Si está autenticado, redirigir al panel de control
    redirect('views/fichaje_view.php');
} else {
    // Si no está autenticado, redirigir al formulario de inicio de sesión
    redirect('views/login_view.php');
}
?>
