<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Nueva Clase</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Registrar nueva clase</h1>
  </div>
  <form action="../controllers/alta_clase_controller.php" method="post" class="form-container">
    <div class="form-group">
      <label for="nombre_clase">Nombre de la clase:</label>
      <input type="text" id="nombre_clase" name="nombre_clase" required><br>
    </div>

    <div class="form-group">
      <label for="precio">Precio:</label>
      <input type="number" id="precio" min="0" name="precio" placeholder="$" required><br>     
    </div>

    <?php if (!empty($profesoresActivos)): ?>
      <div class="form-group">
        <label for="profesor">Profesor/es:</label>
        <select id="profesor" name="profesores[]" size="<?= min(count($profesoresActivos), 5) ?>" multiple>
          <?php foreach ($profesoresActivos as $profesor): ?>
            <option style="padding-right: 150px;" value="<?= $profesor->id_user ?>">
              <?= htmlspecialchars($profesor->user_name) ?>
            </option>
          <?php endforeach; ?>
        </select><br>
      </div>
    <?php endif; ?>

    <div style="padding-top: 150px">
      <button type="submit">
        <i style="padding-right: 10px;" class="fas fa-chalkboard-teacher"></i>
        Registrar Clase
      </button>
      
      <button type="button" onclick="window.location.href='../views/dashboard_view.php'">
        <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
        Volver
      </button>
    </div>
  </form>

  <script src="../public/js/icons.js"></script>

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
      <a href="#" target="_blank" style="color: #66aaff; text-decoration: none;">PC Fighter</a>
    </p>
  </footer>

</body>
</html>
