<?php
session_start();

require_once '../models/user_model.php';

// Recuperar los resultados de la sesión
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
  <title>Búsqueda Fichajes</title>  
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 130px;">Búsqueda de Fichajes</h1>
    <input type="text" id="busqueda_fichaje" placeholder="Buscar Ingreso">
  </div>

  <div class="botonera-1">
    <i class="fas fa-clock"></i>
    <button id="fichaje_manual">Registrar Fichada</button>
  </div>
  
  <div class="tabla">
    <?php if (empty($resultados)) { ?>
      <p>No se encontraron resultados</p>
    <?php } else { ?>
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
          <?php foreach ($resultados as $fichaje): ?>
            <tr>
              <td><?php echo htmlspecialchars($fichaje['rfid']); ?></td>
              <td class="<?php echo $fichaje['dni'] ? 'cerca-de-vencer' : ''; ?>"><?php echo htmlspecialchars($fichaje['dni']); ?></td>
              <td class="diminuto"><?php echo htmlspecialchars($fichaje['alumno']); ?></td>
              <td class="diminuto"><?php echo htmlspecialchars($fichaje['addmission_date']); ?></td>
              <td class="<?php echo $fichaje['pago_cerca'] ? 'cerca-de-vencer' : ''; ?>"><?php echo htmlspecialchars(explode(' ', $fichaje['date_of_renovation'])[0]); ?></td>
            </tr>
          <?php endforeach; ?>
        <?php } ?>
      </tbody>
    </table>
  </div>

  <div class="botonera">
    <button onclick="window.location.href='../views/dashboard_view.php'">
      <i style="padding-right: 10px;" class="fas fa-arrow-left"></i>
      Volver
    </button>

    <button onclick="location.href='../views/dashboard_view.php'">
      <i style="padding-right: 10px;" class="fas fa-home"></i>
      Inicio
    </button>
  </div>

  <script src="../public/js/icons.js"></script>
  <script src="../public/js/busqueda_fichaje.js"></script>
  <script src="../public/js/fichaje_manual.js"></script>

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
      <a href="#" target="_blank" style="color: #66aaff; text-decoration: none;">PC Fighter</a>
    </p>
  </footer>
  
</body>
</html>
