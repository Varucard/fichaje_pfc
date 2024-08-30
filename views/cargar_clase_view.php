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
    <form action="../controllers/alta_usuarios_controller.php" method="post" class="form-container">
      <div class="form-group">
        <label for="nombre_clase">Nombre de la clase:</label>
        <input type="text" id="nombre_clase" name="nombre_clase" required><br>
      </div>

      <div class="form-group">
        <label for="precio">Precio:</label>
        <input type="number" id="precio" name="precio" step="0.01" min="0" placeholder="0.00" required><br>     
      </div>

      <div class="form-group">
        <label for="email">Profesor/es:</label>
        <input type="email" id="email" name="email"><br>
      </div>
    
      <div style="padding-top: 30px">
        <button type="submit">
          <i style="padding-right: 10px;" class="fas fa-user-plus"></i>
          Registrar Clase
        </button>
        
        <button type="button" onclick="window.location.href='dashboard_view.php'">
          <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
          Volver
        </button>
      </div>
    </form>

  <script src="../public/js/icons.js"></script>

</body>
</html>