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
    <h1 style="padding-right: 40px;" >Detalles de la Clase: <?php echo $clase_data->name_class?></h1>
    <button onclick="window.location.href='../controllers/lista_clases_controller.php'">
      <i style="padding-right: 10px; padding-top: 6px" class="fas fa-undo-alt"></i>
      Volver
    </button>
    <button style="color: red" onclick="window.location.href='../controllers/baja_clase_controller.php?dni=<?php echo urlencode($clase_data->id_class); ?>'">
      <i style="color: red; padding-right: 10px; padding-top: 6px" class="fas fa-trash"></i>
      Eliminar Clase
    </button>
  </div>

  <div class="detalle-clase">

    <?php if ($clase_data): ?>
      <form id="form-datos-clase" action="../controllers/actualizar_clase_controller.php" method="post" class="form-container">
        <input type="hidden" id="id" name="id" value="<?php echo htmlspecialchars($clase_data->id_class); ?>">

        <div class="form-group">
          <label for="name_class">Nombre de la case:</label>
          <input type="text" id="name_class" name="name_class" value="<?php echo htmlspecialchars($clase_data->name_class); ?>">

          <label for="precio">Precio de la clase:</label>
          <input type="text" id="precio" name="precio" value="<?php echo htmlspecialchars($clase_data->price_class); ?>" required>

          <button type="submit">
            <i class="fas fa-sync-alt"></i>
            Actualizar Clase
          </button>
        </div>

        <div class="form-group">
          <div class="botonera-1">
            <i class="fas fa-user-plus"></i>
            <button onclick="window.location.href='cargar_usuario_view.php'" style="font-size: 12px; padding-top: 15px; padding-bottom: 11px" id="cargar_usuario">Agregar Cliente/ Profesor</button>
          </div>
        </div>
      </form>

        <?php if (isset($profesores) || isset($clientes)): ?>
        <hr>
        <div class="tabla-container">
          <div class="tabla">
          <h2>Profesores</h2>
            <table id="profesores-table">
              <thead>
                <tr>
                  <th>Profesor</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($profesores as $profesor): ?>
                  <tr>
                    <td><?php echo $profesor->user_name; ?></td>
                    <td>
                      <button style="color: red" class="button_small" onclick="window.location.href='../controllers/baja_clase_controller.php?dni=<?php echo urlencode($usuario['dni']); ?>'">
                        <i style="color: red" class="fas fa-trash"></i>
                        Remover Profesor
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
          <div class="tabla">
          <h2>Alumnos</h2>
            <table id="alumnos-table">
              <thead>
                <tr>
                  <th>Alumno</th>
                  <th>Acción</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($clientes as $cliente): ?>
                  <tr>
                    <td><?php echo $cliente->user_name; ?></td>
                    <td>
                      <button style="color: red" class="button_small" onclick="window.location.href='../controllers/baja_clase_controller.php?dni=<?php echo urlencode($usuario['dni']); ?>'">
                        <i style="color: red" class="fas fa-trash"></i>
                        Remover Alumno
                      </button>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <p>No se encontraron datos para clase.</p>
    <?php endif; ?>
  </div>

  <script src="../public/js/icons.js"></script>
</body>
</html>
