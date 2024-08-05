<?php
session_start();
require_once '../models/user_model.php';

// Recuperar los resultados de la sesión
$resultados = isset($_SESSION['resultados_busqueda']) ? $_SESSION['resultados_busqueda'] : [];

// var_dump($resultados);
// exit;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="../public/css/water.css">
  <link rel="stylesheet" href="../public/css/estilo.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Búsqueda Fichajes - Palillo Fight Club</title>  
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Búsqueda de Fichajes</h1>
    <input type="text" id="busqueda_fichaje" placeholder="Buscar Fichaje">
    <input type="text" id="busqueda_usuario" placeholder="Buscar Cliente">
  </div>
  
  <div class="tabla">
    <table id="tabla-fichajes-busqueda">
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
        <?php if (!empty($resultados)): ?>
          <?php foreach ($resultados as $fichaje): ?>
            <tr>
              <td><?php echo htmlspecialchars($fichaje['rfid']); ?></td>
              <td class="<?php echo $fichaje['dni'] ? 'cerca-de-vencer' : ''; ?>"><?php echo htmlspecialchars($fichaje['dni']); ?></td>
              <td class="diminuto"><?php echo htmlspecialchars($fichaje['alumno']); ?></td>
              <td class="diminuto"><?php echo htmlspecialchars($fichaje['addmission_date']); ?></td>
              <td class="<?php echo $fichaje['pago_cerca'] ? 'cerca-de-vencer' : ''; ?>"><?php echo htmlspecialchars(explode(' ', $fichaje['date_of_renovation'])[0]); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5">No se encontraron resultados</td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <div class="botonera">
    <button onclick="window.location.href='../index.php'">Volver</button>
  </div>

  <script src="../public/js/icons.js"></script>
  <script src="../public/js/busqueda_usuario.js"></script>
  <script src="../public/js/busqueda_fichaje.js"></script>
</body>
</html>
