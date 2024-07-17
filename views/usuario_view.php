<?php
session_start();
require_once '../models/user_model.php';

$user = new User();

// Captura el DNI del usuario de la URL
$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

// Busca los datos del usuario por DNI
$usuario = $user->getUserByDNI($dni);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
  <link rel="stylesheet" href="../public/css/estilo.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalles del Cliente</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Detalle del Usuario</h1>
    <button onclick="window.location.href='busqueda_usuario_view.php'">Volver</button>
    <?php if ($usuario && $usuario[0]['asset'] == 1) { ?>
      <button onclick="window.location.href='../controllers/baja_usuarios_controller.php?dni=<?php echo urlencode($usuario[0]['dni']); ?>'">
        Inhabilitar
      </button>
    <?php } else { ?>
      <button onclick="window.location.href='../controllers/activar_usuario_controller.php?dni=<?php echo urlencode($usuario[0]['dni']); ?>'">
        Activar
      </button>
    <?php } ?>
  </div>

  <div class="detalle-usuario">
    <?php if ($usuario): ?>
      <!-- Formulario para los datos del usuario -->
      <form id="form-datos-usuario" action="../controllers/actualizar_usuarios_controller.php" method="post" class="form-container">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario[0]['id_user']); ?>">

        <div class="form-group">
          <label for="rfid">N° de llavero:</label>
          <input type="text" id="rfid" name="rfid" value="<?php echo htmlspecialchars($usuario[0]['rfid']); ?>">

          <label for="dni">N° de DNI:</label>
          <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($usuario[0]['dni']); ?>">

          <label for="name">Nombre:</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($usuario[0]['name']); ?>">

          <input type="hidden" name="id" value="<?php echo htmlspecialchars($usuario[0]['id_user']); ?>">
          <button type="submit">Actualizar</button>
        </div>

        <div class="form-group">
          <label for="surname">Apellido:</label>
          <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($usuario[0]['surname']); ?>">

          <label for="birth_day">Fecha de Nacimiento:</label>
          <input type="date" id="birth_day" name="birth_day" value="<?php echo htmlspecialchars($usuario[0]['birth_day']); ?>">
        </div>

        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario[0]['email']); ?>">

          <label for="phone">Teléfono:</label>
          <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($usuario[0]['phone_number']); ?>">
        </div>
      </form>
    <?php else: ?>
      <p>No se encontraron datos para este usuario.</p>
    <?php endif; ?>
  </div>
</body>
</html>
