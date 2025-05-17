<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lista de Profesores</title>  
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1>Lista de Profesores</h1>
    <input style="margin-left: 230px; margin-top: 15px;" type="text" id="busqueda_usuario" name="dni" placeholder="Buscar Cliente/ Profesor">
  </div>

  <div class="botonera-1">
    <i class="fas fa-user-plus"></i>
    <button onclick="window.location.href='../views/cargar_usuario_view.php'" id="cargar_usuario">Agregar Profesor</button>
  </div>
  
  <div class="tabla">
    <?php if (empty($resultados)) { ?>
      <p>No se encontraron resultados</p>
    <?php } else { ?>
      <table>
        <thead>
          <tr>
            <th>DNI</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultados as $usuario) { ?>
            <tr>
              <td style="color:red"><?php echo htmlspecialchars($usuario['dni'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($usuario['user_name'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($usuario['user_surname'] ?? '', ENT_QUOTES, 'UTF-8'); ?></td>
              <td>
                <button class="button_small" onclick="window.location.href='../controllers/detalle_usuario_controller.php?dni=<?php echo urlencode($usuario['dni']); ?>'">
                  <i class="fas fa-user"></i>
                  Ver +
                </button>
                <?php if ($usuario['asset'] == 1) { ?>
                  <button style="color: red" class="button_small" onclick="window.location.href='../controllers/baja_usuarios_controller.php?dni=<?php echo urlencode($usuario['dni']); ?>'">
                    <i style="color: red" class="fas fa-trash"></i>
                    Desactivar
                  </button>
                <?php } ?>
                <?php if ($usuario['asset'] == 0) { ?>
                  <p style="color:red">Profesor inactivo</p>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </div>

  <div class="botonera">
    <button onclick="location.href='../views/dashboard_view.php'">
      <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
      Volver
    </button>

    <button onclick="location.href='../views/dashboard_view.php'">
      <i style="padding-right: 10px;" class="fas fa-home"></i>
      Inicio
    </button>
  </div>

  <script src="../public/js/icons.js"></script>
  <script src="../public/js/busqueda_usuario.js"></script>

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
