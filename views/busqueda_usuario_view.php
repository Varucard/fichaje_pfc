<?php
session_start();
require_once '../models/user_model.php';

$user = new User();

// Inicializar resultados vacíos
$resultados = [];

// Verifica si se ha realizado una acción reciente (dar de baja/activar)
if (isset($_GET['reload'])) {
  // Recuperar los resultados anteriores de la sesión
  if (isset($_SESSION['resultados_busqueda'])) {
    $resultados = $_SESSION['resultados_busqueda'];
  }
} else {
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dni = isset($_POST['dni']) ? $_POST['dni'] : '';
    $resultados = $user->getUserByDNI($dni);
    $_SESSION['resultados_busqueda'] = $resultados; // Almacenar resultados en la sesión
  }

  // Si no hay resultados en la sesión, intentar recuperarlos del POST
  if (empty($resultados) && isset($_SESSION['resultados_busqueda'])) {
    $resultados = $_SESSION['resultados_busqueda'];
  }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
  <link rel="stylesheet" href="../public/css/estilo.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Búsqueda de Usuarios - Palillo Fight Club</title>  
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Búsqueda de Usuarios</h1>
    <input type="text" id="busqueda_usuario" name="dni" placeholder="Buscar Cliente">
  </div>
  
  <div class="tabla">
    <?php if (empty($resultados)) { ?>
      <p>No se encontraron resultados</p>
    <?php } else { ?>
      <table>
        <thead>
          <tr>
            <th>RFID</th>
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
              <td><?php echo htmlspecialchars($usuario['name'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($usuario['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td>
                <button class="button_small" onclick="window.location.href='usuario_view.php?dni=<?php echo urlencode($usuario['dni']); ?>'">
                  <i class="fas fa-user"></i>
                </button>
                <?php if ($usuario['asset'] == 1) { ?>
                  <button class="button_small" onclick="window.location.href='../controllers/baja_usuarios_controller.php?dni=<?php echo urlencode($usuario['dni']); ?>'">
                    <i class="fas fa-trash"></i>
                  </button>
                <?php } ?>
                <?php if ($usuario['asset'] == 0) { ?>
                  <td style="color:red">Usuario inactivo</td>
                <?php } ?>
              </td>
            </tr>
          <?php } ?>
        </tbody>
      </table>
    <?php } ?>
  </div>

  <div class="botonera">
    <button onclick="window.location.href='../index.php'">Volver</button>
  </div>

  <script src="../public/js/busqueda_usuario.js"></script>
</body>
</html>
