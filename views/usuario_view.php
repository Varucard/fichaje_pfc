<?php
session_start();
require_once '../models/user_model.php';
require_once '../models/pago_model.php';

$user = new User();
$pagos = new Pagos();

// Captura el DNI del usuario de la URL
$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

// Busca los datos del usuario por DNI
$usuario = $user->getUserByDNI($dni);

$pago = $pagos->getPagosByUser($usuario[0]['id_user']);

// Limitar los pagos a los 10 registros más recientes y doy vuelta el arreglo
$pago = array_slice($pago, 0, 5);
$pago = array_reverse($pago);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilo.css">
  <link rel="stylesheet" href="../public/css/min.css">
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
        Inhabilitar Usuario
      </button>
    <?php } else { ?>
      <button onclick="window.location.href='../controllers/activar_usuario_controller.php?dni=<?php echo urlencode($usuario[0]['dni']); ?>'">
        Activar Usuario
      </button>
    <?php } ?>
  </div>


  <div class="detalle-usuario">

    <?php if ($usuario && $usuario[0]['asset'] == 0) { ?>
      <p style="color: red;">Usuario inactivo</p>
    <?php } ?>

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

          <?php if ($usuario && $usuario[0]['asset'] == 1) { ?>
            <button type="submit">Actualizar Usuario</button>
          <?php } ?>
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

      <hr>

      <!-- Tabla de Pagos -->
      <h2 style="margin-right: 50px;">Historial de Pagos</h2>
      <?php if ($usuario[0]['asset'] == 1) { ?>
        <button onclick="window.location.href='../controllers/nuevo_pago_controller.php?id_user=<?php echo $usuario[0]['id_user']; ?>'" id="cargar_pago">Renovar pago</button>
      <?php } ?>
      <button onclick="window.location.href='../views/fichaje_view.php'">Inicio</button>
      <table id="pagos-table">
        <thead>
          <tr>
            <th>Fecha de Pago</th>
            <th>Fecha de Renovación</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pago as $p): ?>
            <tr>
              <td><?php echo htmlspecialchars($p['discharge_date']); ?></td>
              <td><?php echo htmlspecialchars($p['date_of_renovation']); ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>

    <?php else: ?>
      <p>No se encontraron datos para este usuario.</p>
    <?php endif; ?>
  </div>
</body>
</html>
