<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
  <link rel="stylesheet" href="../public/css/estilo.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Palillo Fight Club</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Agregar nuevo cliente</h1>
  </div>
    <form action="../controllers/cargar_usuario.php" method="post" class="form-container">
      <div class="form-group">
        <label for="rfid">N° de llavero:</label>
        <input type="text" id="rfid" name="rfid"><br>

        <label for="dni">N° de DNI:</label>
        <input type="int" id="dni" name="dni"><br>
      </div>

      <div class="form-group">
        <label for="name">Nombre:</label>
        <input type="text" id="name" name="name"><br>
        
        <label for="surname">Apellido:</label>
        <input type="text" id="surname" name="surname"><br>
      </div>

      <div class="form-group">
        <label for="email">Email:</label>
        <input type="email" id="email" name="email"><br>

        <label for="email">Telefono:</label>
        <input type="int" id="phone" name="phone">
      </div>

      <input type="submit" value="Cargar Cliente">
      <button type="button" onclick="window.location.href='fichaje_views.php'">Volver</button>
    </form>
  </div>
</body>
</html>