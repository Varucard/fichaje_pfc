<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <link rel="stylesheet" href="../public/css/festejados.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ultimos Ingresos</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px; text-align: center;">Últimos Ingresos</h1>
    <input type="text" id="busqueda_fichaje" placeholder="Buscar Ingreso">
    <input type="text" id="busqueda_usuario" placeholder="Buscar Cliente/ Profesor">
  </div>
  <div class="tabla">
    <button id="fichaje_manual">
      <i style="padding-right: 10px;" class="fas fa-clock"></i>
      Registrar Fichada
    </button>
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
    <button onclick="window.location.href='../views/dashboard_view.php'">
      <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
      Volver
    </button>
  </div>

  <!-- Div para el cartel de cumpleaños -->
  <div id="cumpleanosModal" class="modal">
    <div class="modal-content">
      <span class="close">&times;</span>
      <p id="cumpleanosTexto"></p>
    </div>
  </div>

  <script src="../public/js/busqueda_usuario.js"></script>
  <script src="../public/js/busqueda_fichaje.js"></script>
  <script src="../public/js/ultimos_fichajes.js"></script> <!-- Contiene para evitar inconvenientes el checkeador de nuevos UID -->
  <script src="../public/js/fichaje_manual.js"></script>
  <script src="../public/js/festejados.js"></script>
  <script src="../public/js/icons.js"></script>
</body>
</html>
