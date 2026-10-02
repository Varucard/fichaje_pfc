<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Nuevo Cliente/ Profesor</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Registrar nuevo Cliente/ Profesor</h1>
  </div>
    <form action="../controllers/alta_usuarios_controller.php" method="post" class="form-container">
      <div class="form-group">
        <label for="rfid">N° de llavero:</label>
        <input type="text" id="rfid" name="rfid" required><br>

        <label for="dni">N° de DNI:</label>
        <input type="int" id="dni" name="dni" required><br>

        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" required><br>
      </div>

      <div class="form-group">
        <label for="surname">Apellido:</label>
        <input type="text" id="surname" name="surname"><br>

        <label for="birth_day">Fecha de nacimiento:</label>
        <input type="date" id="birth_day" name="birth_day"><br>        
      </div>

      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email"><br>

        <label for="email">Teléfono:</label>
        <input type="int" id="phone" name="phone">
      </div>

      <div class="exceptuado">
        <label for="pago">¿Agregar Cliente con pago?</label>
        <input type="checkbox" id="pago" name="pago" value="TRUE">
        
        <label for="profesor">¿Profesor?</label>
        <input type="checkbox" id="profesor" name="profesor" value="TRUE">
      </div>
    
      <div style="padding-top: 30px">
        <button type="submit">
          <i style="padding-right: 10px;" class="fas fa-user-plus"></i>
          Registrar cliente/ Profesor
        </button>
        
        <button type="button" onclick="window.location.href='dashboard_view.php'">
          <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
          Volver
        </button>
      </div>
    </form>

  <script src="../public/js/icons.js"></script>
  <script src="../public/js/marcar_un_solo_checkbox.js"></script>

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
