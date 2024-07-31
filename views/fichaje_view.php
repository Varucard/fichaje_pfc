<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilo.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Palillo Fight Club</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Fichaje Fight Club Palillo</h1>
    <input type="text" id="busqueda_fichaje" placeholder="Buscar Fichaje" onkeyup="buscarFichaje()">
    <input type="text" id="busqueda_usuario" placeholder="Buscar Cliente">
  </div>
  <h1 style="text-align: center;">Últimos fichajes</h1>
  <div class="tabla">
    <table id="tabla-fichajes" class="water-table">
      <thead>
        <tr>
          <th>N° de Llavero</th>
          <th>N° de DNI</th>
          <th>Alumno</th>
          <th>Ingreso</th>
          <th>Fecha de pago</th>
        </tr>
      </thead>
      <tbody>
      <!-- Los fichajes serán cargados dinámicamente -->
      </tbody>
    </table>
  </div>

  <div class="botonera">
    <button onclick="window.location.href='cargar_usuario_view.php'" id="cargar_usuario">Agregar Cliente</button>
  </div>
  <script src="../public/js/busqueda_usuario.js"></script>
  <script src="../public/js/busqueda_fichaje.js"></script>
  <script src="../public/js/ultimos_fichajes.js"></script>
</body>
</html>
