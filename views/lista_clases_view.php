<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Clases</title>  
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 250px;">Lista de Clases</h1>
    <input type="text" id="busqueda_clase" placeholder="Buscar Clase">
  </div>

  <div class="botonera-1">
    <i class="fas fa-chalkboard-teacher"></i>
    <button onclick="window.location.href='../controllers/cargar_clase_controller.php'">Agregar Clase</button>
  </div>
  
  <div class="tabla">
    <table id="tabla-clases">
      <thead>
        <tr>
          <th>Nombre de la Clase</th>
          <th>Precio</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      <?php if (!empty($clases)): ?>
        <?php foreach ($clases as $clase): ?>
          <tr>
            <td class="diminuto"><?php echo htmlspecialchars($clase->name_class); ?></td>
            <td class="diminuto">$<?php echo htmlspecialchars(number_format($clase->price_class, 0, ',', '.')); ?></td>
            <td>
              <button class="button_small" onclick="window.location.href='../controllers/detalle_clase_controller.php?id_class=<?php echo urlencode($clase->id_class); ?>'">
                <i class="fas fa-eye"></i>
                Ver +
              </button>
              <button style="color: red" class="button_small" onclick="window.location.href='../controllers/baja_clase_controller.php?id_class=<?php echo urlencode($clase->id_class); ?>'">
                <i style="color: red" class="fas fa-trash"></i>
                Eliminar
              </button>
            </td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr>
          <td colspan="4">No se encontraron clases</td>
        </tr>
      <?php endif; ?>
    </tbody>
    </table>
  </div>

  <div class="botonera">
    <button onclick="window.location.href='../views/dashboard_view.php'">
      <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
      Volver
    </button>
  </div>

  <script src="../public/js/icons.js"></script>
</body>
</html>
