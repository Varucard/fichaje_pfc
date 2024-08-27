<?php
session_start();

require_once '../helpers/url_helper.php';

// Inicializa resultados si no hay en la sesión
$resultados = isset($_SESSION['resultados_busqueda']) ? $_SESSION['resultados_busqueda'] : [];
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilos.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Búsqueda de Usuarios</title>  
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1>Búsqueda de Usuarios</h1>
    <input style="margin-left: 100px; margin-top: 15px;" type="text" id="busqueda_usuario" name="dni" placeholder="Buscar Cliente/ Usuario">
  </div>
  
  <div class="tabla">
    <?php if (empty($resultados)) { ?>
      <p>No se encontraron resultados</p>
    <?php } else { ?>
      <table>
        <thead>
          <tr>
            <th>LLAVERO</th>
            <th>DNI</th>
            <th>Nombre</th>
            <th>Apellido</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultados as $usuario) { ?>
            <tr>
              <td><?php echo htmlspecialchars($usuario['rfid'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td style="color:red"><?php echo htmlspecialchars($usuario['dni'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($usuario['user_name'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($usuario['user_surname'], ENT_QUOTES, 'UTF-8'); ?></td>
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
                  <p style="color:red">Usuario inactivo</p>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </div>

  <div class="botonera">
    <button onclick="location.href='../controllers/busqueda_usuario_controller.php'">
      <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
      Volver
    </button>
  </div>

  <script src="../public/js/icons.js"></script>
  <script src="../public/js/busqueda_usuario.js"></script>
</body>
</html>
