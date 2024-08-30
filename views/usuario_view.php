<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalles del <?php echo $usuario[0]['type_user'] == 3 ? 'Profesor' : 'Cliente'; ?></title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="padding-right: 40px;" >Detalle del <?php echo $usuario[0]['type_user'] == 3 ? 'Profesor' : 'Cliente'; ?></h1>
    <button onclick="window.location.href='../views/busqueda_usuario_view.php'">
      <i style="padding-right: 10px; padding-top: 6px" class="fas fa-undo-alt"></i>
      Volver
    </button>
    <?php if ($usuario && $usuario[0]['asset'] == 1): ?>
      <button style="color: red" onclick="window.location.href='../controllers/baja_usuarios_controller.php?dni=<?php echo urlencode($usuario[0]['dni']); ?>'">
        <i style="color: red; padding-right: 10px; padding-top: 6px" class="fas fa-trash"></i>
        Inhabilitar <?php echo $usuario[0]['type_user'] == 3 ? 'Profesor' : 'Cliente'; ?>
      </button>
    <?php else: ?>
      <button onclick="window.location.href='../controllers/activar_usuario_controller.php?dni=<?php echo urlencode($usuario[0]['dni']); ?>'">
        <i style="padding-right: 10px; padding-top: 6px" class="fas fa-user-plus"></i>
        Re-activar <?php echo $usuario[0]['type_user'] == 3 ? 'Profesor' : 'Cliente'; ?>
      </button>
    <?php endif; ?>
  </div>

  <div class="detalle-usuario">
    <?php if ($usuario && $usuario[0]['asset'] == 0): ?>
      <p class="status-inactive"><?php echo $usuario[0]['type_user'] == 3 ? 'Profesor inactivo' : 'Cliente inactivo'; ?></p>
    <?php endif; ?>

    <?php if ($usuario): ?>
      <form id="form-datos-usuario" action="../controllers/actualizar_usuarios_controller.php" method="post" class="form-container">
        <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($usuario[0]['id_user']); ?>">

        <div class="form-group">
          <label for="rfid">N° de llavero:</label>
          <input type="text" id="rfid" name="rfid" value="<?php echo htmlspecialchars($usuario[0]['rfid']); ?>">

          <label for="dni">N° de DNI:</label>
          <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($usuario[0]['dni']); ?>">

          <label for="name">Nombre:</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($usuario[0]['user_name']); ?>">

          <?php if ($usuario[0]['asset'] == 1): ?>
            <button type="submit">
              <i class="fas fa-sync-alt"></i>
              Actualizar <?php echo $usuario[0]['type_user'] == 3 ? 'Profesor' : 'Cliente'; ?>
            </button>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="surname">Apellido:</label>
          <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($usuario[0]['user_surname']); ?>">

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

      <?php if ($usuario[0]['type_user'] != 3): ?>
        <hr>
        <h2>Historial de Pagos</h2>
        <?php if ($usuario[0]['asset'] == 1): ?>
          <button onclick="window.location.href='../controllers/nuevo_pago_controller.php?id_user=<?php echo $usuario[0]['id_user']; ?>'" id="cargar_pago">
            <i style="padding-right: 10px;" class="fas fa-wallet"></i>
            Renovar pago
          </button>
          <button id="cargar_pago_manual">
            <i class="fas fa-wallet"></i>
            <i style="padding-right: 10px;" class="fas fa-hand-paper"></i>
            Pago manual
          </button>
        <?php endif; ?>
        <button onclick="location.href='../views/dashboard_view.php'">
          <i style="padding-right: 10px;" class="fas fa-home"></i>
          Inicio
        </button>
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
                <td><?php echo date('d-m-Y', strtotime($p['discharge_date'])); ?></td>
                <td><?php echo date('d-m-Y', strtotime($p['date_of_renovation'])); ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      <?php endif; ?>

    <?php else: ?>
      <p>No se encontraron datos para este <?php echo $usuario[0]['type_user'] == 3 ? 'Profesor' : 'Cliente'; ?>.</p>
    <?php endif; ?>
  </div>

  <script src="../public/js/pago_manual.js"></script>
  <script src="../public/js/icons.js"></script>
</body>
</html>
