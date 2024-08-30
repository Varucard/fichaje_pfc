<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <link rel="stylesheet" href="../public/css/festejados.css">

  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Panel del Administrador</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="200" height="200">
    <h1 style="margin-left: 60px; text-align: center;">Fight Club Palillo</h1>
  </div>
  <h2 style="text-align: center;">Bienvenido Administrador!</h2>

  <!-- Busqueda -->
  <div class="search-container">
    <i class="fas fa-search"></i>
    <input type="text" id="busqueda_fichaje" placeholder="Buscar Ingreso"> 
  </div>
  <div class="search-container">
    <i class="fas fa-search"></i>
    <input type="text" id="busqueda_usuario" placeholder="Buscar Cliente/ Profesor">
  </div>
  <div class="search-container">
    <i class="fas fa-search"></i>
    <input type="text" id="busqueda_clase" placeholder="Buscar Clase">
  </div>

  <!-- Botones de acciones -->
  <div>
    <div class="botonera-1">
      <i class="fas fa-clock"></i>
      <button id="fichaje_manual">Registrar Fichada</button>
    </div>
    <div class="botonera-1">
      <i class="fas fa-user-plus"></i>
      <button onclick="window.location.href='cargar_usuario_view.php'" style="font-size: 12px; padding-top: 15px; padding-bottom: 11px" id="cargar_usuario">Agregar Cliente/ Profesor</button>
    </div>
    <div class="botonera-1">
      <i class="fas fa-wallet"></i>
      <button style="font-size: 14px; padding-top: 13px; padding-bottom: 11px" id="cargar_pago_manual_usuario">Abonar Clase</button>
    </div>
    <div class="botonera-1">
      <i class="fas fa-chalkboard-teacher"></i>
      <button onclick="window.location.href='cargar_clase_view.php'" id="cargar_clase">Agregar Clase</button>
    </div>
    <div class="botonera-1">
      <i class="fas fa-chalkboard-teacher"></i>
      <button onclick="window.location.href='cargar_usuario_view.php'" id="cargar_clase">Clases?</button>
    </div>
    <div class="botonera-1">
      <i class="fas fa-chalkboard-teacher"></i>
      <button onclick="window.location.href='cargar_usuario_view.php'" id="cargar_clase">Clientes?</button>
    </div>
    <div class="botonera-1">
      <i class="fas fa-clipboard-check"></i>
      <button onclick="window.location.href='fichajes_view.php'">Ultimos Ingresos</button>
    </div>
    <div class="botonera-1">
      <i class="fas fa-chalkboard-teacher"></i>
      <button onclick="window.location.href='cargar_usuario_view.php'" id="cargar_clase">Deuda?</button>
    </div>
    <div class="botonera-1">
      <i class="fas fa-chalkboard-teacher"></i>
      <button onclick="window.location.href='cargar_usuario_view.php'" id="cargar_clase">Liquidar?</button>
    </div>
  </div>

  <!-- Botones que pueden romper cosas -->
  <div class="">
    <div class="botonera-2">
      <i style="color: red;" class="fas fa-sync-alt"></i>
      <button style="color: red;" onclick="window.location.href='../config/reiniciar_arduino.php'" >Reiniciar Arduino</button>
    </div>
    <div class="botonera-2">
      <i style="color: red;" class="fas fa-sign-out-alt"></i>
      <form action="../controllers/auth_controller.php?action=logout" method="POST">
        <button style="color: red;">Cerrar Sesión</button>
      </form>
    </div>
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
  <!-- <script src="../public/js/ultimos_fichajes.js"></script> Contiene para evitar inconvenientes el checkeador de nuevos UID -->
  <script src="../public/js/fichaje_manual.js"></script>
  <script src="../public/js/festejados.js"></script>
  <script src="../public/js/icons.js"></script>
  <script src="../public/js/pago_manual_user.js"></script>
</body>
</html>
