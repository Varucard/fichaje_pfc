<?php
  require_once '../helpers/session_helper.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <title>Inicio de Sesión</title>
  <link rel="stylesheet" href="../public/css/estilos.css">
  <link rel="stylesheet" href="../public/css/water.css">
</head>
<body>
  <div class="login-container">
    <img src="../public/img/logo.png" alt="Palillo Fight Club">
    <h1>Iniciar Sesión</h1>

    <?php flash('login_error'); ?>

    <form action="../controllers/auth_controller.php?action=login" method="POST">
      <div>
        <label for="dni">Nro. de Documento:</label>
        <input type="text" name="dni" id="dni" required>
      </div>
      <div>
        <label for="password">Contraseña:</label>
        <input type="password" name="password" id="password" required>
      </div>
      <button type="submit">Iniciar Sesión</button>
    </form>
  </div>

  <div style="height: 100px;"></div> 

  <footer style="
    position: fixed !important;
    bottom: 0 !important;
    left: 0 !important;
    width: 100% !important;
    background-color: rgb(17, 17, 50) !important;
    color: #eee !important; /* letras claras */
    text-align: center !important;
    padding: 20px 10px !important;
    font-size: 0.9rem !important;
    border-top: 1px solid #444 !important; /* borde un poco más oscuro para no destacar demasiado */
    font-family: Arial, sans-serif !important;
    z-index: 9999 !important;
  ">
    <p style="margin: 0;">&copy; <?php echo date('Y'); ?> Palillo Fight Club. Todos los derechos reservados.</p>
    <p style="margin: 0;">
      Desarrollado por
      <a href="#" target="_blank" style="color: #66aaff; text-decoration: none;">PC Fighter</a>
    </p>
  </footer>
  
</body>
</html>
