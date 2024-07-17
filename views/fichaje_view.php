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
    <h1 style="margin-right: 50px;">Fichaje Fight Club Palillo</h1>
    <input type="text" id="busqueda_fichaje" placeholder="Buscar fichaje">
    <input type="text" id="busqueda_usuario" placeholder="Buscar Cliente">
  </div>
  <div class="tabla">
    <h3>Ultimos fichajes</h3>
    <table>
      <thead>
        <tr>
          <th>Llavero</th>
          <th>DNI</th>
          <th>Alumno</th>
          <th>Ingreso</th>
          <th>Fecha de pago</th>
        </tr>
      </thead>
      <tbody>
        <tr>
          <td>1528162516</td>
          <td>41550112</td>
          <td>Cristian Marquez</td>
          <td>10/12/2024</td>
          <td>10/01/2025</td>
        </tr>
      </tbody>
    </table>
  </div>
  <div class="botonera">
    <button onclick="window.location.href='cargar_usuario_view.php'" id="cargar_usuario">Agregar Cliente</button>
  </div>
  <script src="../public/js/busqueda_usuario.js"></script>
</body>
</html>