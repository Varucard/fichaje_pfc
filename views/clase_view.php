<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalles de la Clase</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="padding-right: 40px;" >Detalle de la Clase: <?php echo $clase_data->name_class?></h1>
    <button onclick="window.location.href='../views/busqueda_clases_view.php'">
      <i style="padding-right: 10px; padding-top: 6px" class="fas fa-undo-alt"></i>
      Volver
    </button>
    <button style="color: red" onclick="window.location.href='../controllers/baja_clase_controller.php?dni=<?php echo urlencode($clase_data->id_class); ?>'">
      <i style="color: red; padding-right: 10px; padding-top: 6px" class="fas fa-trash"></i>
      Eliminar Clase
    </button>

  <div class="detalle-usuario">

    <?php if ($clase_data): ?>
      <form id="form-datos-clase" action="../controllers/actualizar_clase_controller.php" method="post" class="form-container">
        <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($clase_data->id_class); ?>">

        <div class="form-group">
          <label for="rfid">Nombre de la case:</label>
          <input type="text" id="name_class" name="name_class" value="<?php echo htmlspecialchars($clase_data->name_class); ?>">

          <label for="dni">Precio de la clase:</label>
          <input type="number" id="precio" name="precio" value="<?php echo htmlspecialchars($clase_data->price_class); ?>">

          <button type="submit">
            <i class="fas fa-sync-alt"></i>
            Actualizar Clase
          </button>
        </div>

    <?php else: ?>
      <p>No se encontraron datos para clase.</p>
    <?php endif; ?>
  </div>

  <script src="../public/js/icons.js"></script>
</body>
</html>
