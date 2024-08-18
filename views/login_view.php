<?php
  require_once '../helpers/session_helper.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <title>Iniciar Sesión - Palillo Fight Club</title>
  <link rel="stylesheet" href="../public/css/estilo.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
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
</body>
</html>
