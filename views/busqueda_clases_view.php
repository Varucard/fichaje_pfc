<?php
// Recuperar los resultados de la sesión
$resultados = isset($_SESSION['resultados_busqueda']) ? $_SESSION['resultados_busqueda'] : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Búsqueda Clases</title>  
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 150px;">Búsqueda de Clases</h1>
    <input type="text" id="busqueda_clase" placeholder="Buscar clase">
  </div>
  
  <div class="tabla">
    <table id="tabla-fichajes-busqueda">
      <thead>
        <tr>
          <th>Nombre de la clase</th>
          <th>Precio</th>
          <th>Profesor</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($clases)): ?>
          <?php foreach ($clases as $c): ?>
            <tr>
              <td><?php echo htmlspecialchars($c->name_class); ?></td>
              <td><?php echo htmlspecialchars($c->price_class); ?></td>
              <td class="diminuto">Nombre del profesor tanto</td>
              <td class="diminuto">
                <button class="button_small" onclick="window.location.href='../controllers/detalle_clase_controller.php?id_class=<?php echo urlencode($c->id_class); ?>'">
                  <i class="fas fa-eye"></i>
                  Ver +
                </button>
                <button style="color: red" class="button_small" onclick="window.location.href='../controllers/baja_clase_controller.php?id_class=<?php echo urlencode($c->id_class); ?>'">
                  <i style="color: red" class="fas fa-trash"></i>
                  Eliminar
                </button>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">No se encontraron resultados</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="botonera">
    <button onclick="location.href='../controllers/busqueda_clase_controller.php'">
      <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
      Volver
    </button>

    <button onclick="location.href='../views/dashboard_view.php'">
      <i style="padding-right: 10px;" class="fas fa-home"></i>
      Inicio
    </button>
  </div>

  <script src="../public/js/icons.js"></script>
  <script src="../public/js/busqueda_clase.js"></script>
</body>
</html>
