<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrar Nuevo Cliente</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Registrar nuevo cliente</h1>
  </div>
    <form action="../controllers/alta_usuarios_controller.php" method="post" class="form-container">
      <div class="form-group">
        <label for="rfid">N° de llavero:</label>
        <input type="text" id="rfid" name="rfid" required><br>

        <label for="dni">N° de DNI:</label>
        <input type="int" id="dni" name="dni" required><br>

        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name" required><br>

        <label class="exceptuado" for="pago">¿Agregar Usuario con pago?</label>
        <input class="exceptuado" type="checkbox" id="pago" name="pago" value="TRUE">
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

      
      <button type="submit">
        <i style="padding-right: 10px;" class="fas fa-user-plus"></i>
        Registrar cliente
      </button>
      
      <button type="button" onclick="window.location.href='dashboard_view.php'">
        <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
        Volver
      </button>
    </form>
  </div>

  <script src="../public/js/icons.js"></script>

</body>
</html>