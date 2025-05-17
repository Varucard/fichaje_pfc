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
              <td><?php echo '$' . number_format($c->price_class, 0, ',', '.'); ?></td>
              <td class="diminuto">
                <?php
                  $nombresProfesores = [];
                  if (!empty($profesoresPorClase[$c->id_class])) {
                    foreach ($profesoresPorClase[$c->id_class] as $profesor) {
                      $nombresProfesores[] = htmlspecialchars($profesor['user_name']);
                    }
                  }
                  echo implode(', ', $nombresProfesores);
                ?>
              </td>
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
      <a href="https://tusitio.dev" target="_blank" style="color: #66aaff; text-decoration: none;">PC Fighter</a>
    </p>
  </footer>
  
</body>
</html>
