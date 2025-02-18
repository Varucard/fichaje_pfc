<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalles del <?php echo $usuario['type_user'] == 1 ? 'Profesor' : 'Cliente'; ?></title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="padding-right: 40px;" >Detalle del <?php echo $usuario['type_user'] == 1 ? 'Profesor' : 'Cliente'; ?></h1>
    <button onclick="window.location.href='../controllers/lista_<?php echo $usuario['type_user'] == 1 ? 'profesores' : 'clientes'; ?>_controller.php'">
      <i style="padding-right: 10px; padding-top: 6px" class="fas fa-undo-alt"></i>
      Volver
    </button>
    <?php if ($usuario && $usuario['asset'] == 1): ?>
      <button style="color: red" onclick="window.location.href='../controllers/baja_usuarios_controller.php?dni=<?php echo urlencode($usuario['dni']); ?>'">
        <i style="color: red; padding-right: 10px; padding-top: 6px" class="fas fa-trash"></i>
        Inhabilitar <?php echo $usuario['type_user'] == 1 ? 'Profesor' : 'Cliente'; ?>
      </button>
    <?php else: ?>
      <button onclick="window.location.href='../controllers/activar_usuario_controller.php?dni=<?php echo urlencode($usuario['dni']); ?>'">
        <i style="padding-right: 10px; padding-top: 6px" class="fas fa-user-plus"></i>
        Re-activar <?php echo $usuario['type_user'] == 1 ? 'Profesor' : 'Cliente'; ?>
      </button>
    <?php endif; ?>
  </div>

  <div class="detalle-usuario">
    <?php if ($usuario && $usuario['asset'] == 0): ?>
      <p class="status-inactive"><?php echo $usuario['type_user'] == 1 ? 'Profesor inactivo' : 'Cliente inactivo'; ?></p>
    <?php endif; ?>

    <?php if ($usuario): ?>
      <form id="form-datos-usuario" action="../controllers/actualizar_usuarios_controller.php" method="post" class="form-container">
        <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($usuario['id_user']); ?>">

        <input type="hidden" id="type_user" name="type_user" value="<?php echo htmlspecialchars($usuario['type_user']); ?>">

        <div class="form-group">
          <label for="rfid">N° de llavero:</label>
          <input type="text" id="rfid" name="rfid" value="<?php echo htmlspecialchars($usuario['rfid']); ?>">

          <label for="dni">N° de DNI:</label>
          <input type="text" id="dni" name="dni" value="<?php echo htmlspecialchars($usuario['dni']); ?>">

          <label for="name">Nombre:</label>
          <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($usuario['user_name']); ?>">

          <?php if ($usuario['asset'] == 1): ?>
            <div style="padding-top: 15px; padding-bottom: 15px;" class="exceptuado">
              <label for="profesor"><?php echo $usuario['type_user'] != 1 ? '¿Profesor?' : '¿Cliente?'; ?></label>
              <input type="checkbox" id="cambioTipoUsuario" name="cambioTipoUsuario-" value="TRUE">
            </div>
          <?php endif; ?>

          <?php if ($usuario['asset'] == 1): ?>
            <button type="submit">
              <i class="fas fa-sync-alt"></i>
              Actualizar <?php echo $usuario['type_user'] == 1 ? 'Profesor' : 'Cliente'; ?>
            </button>
          <?php endif; ?>

          <?php if ($usuario['type_user'] == 1): ?>
            <div class="botonera-1">
              <i class="fas fa-chalkboard-teacher"></i>
              <button>Liquidar</button>
            </div>
          <?php endif; ?>
        </div>

        <div class="form-group">
          <label for="surname">Apellido:</label>
          <input type="text" id="surname" name="surname" value="<?php echo htmlspecialchars($usuario['user_surname']); ?>">

          <label for="birth_day">Fecha de Nacimiento:</label>
          <input type="date" id="birth_day" name="birth_day" value="<?php echo htmlspecialchars($usuario['birth_day']); ?>">
        </div>

        <div class="form-group">
          <label for="email">Email:</label>
          <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>">

          <label for="phone">Teléfono:</label>
          <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($usuario['phone_number']); ?>">
        </div>
      </form>

      <?php if ($usuario['type_user'] != 1): ?>
        <hr>
        <h2>Historial de Pagos</h2>
        <?php if ($usuario['asset'] == 1): ?>
          <button onclick="window.location.href='../controllers/nuevo_pago_controller.php?id_user=<?php echo $usuario['id_user']; ?>'" id="cargar_pago">
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
      <p>No se encontraron datos para este <?php echo $usuario['type_user'] == 1 ? 'Profesor' : 'Cliente'; ?>.</p>
    <?php endif; ?>
  </div>

  <script src="../public/js/pago_manual.js"></script>
  <script src="../public/js/icons.js"></script>
</body>
</html>
