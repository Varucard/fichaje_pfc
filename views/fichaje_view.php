<?php
require_once '../models/fichaje_model.php';

$fichajesModel = new Fichajes();
$ultimosFichajes = $fichajesModel->getUltimosFichajes();

$fichajesConPago = [];
foreach ($ultimosFichajes as $fichaje) {
  $fechaPago = $fichajesModel->getUltimaFechaPago($fichaje['id_user']);
  $fichaje['date_of_renovation'] = $fechaPago;

  // Calcular la diferencia de días entre la fecha de pago y la fecha actual
  if ($fechaPago) {
    $fechaActual = new DateTime();
    $fechaPagoDateTime = new DateTime($fechaPago);
    $diferenciaDias = $fechaPagoDateTime->diff($fechaActual)->days;
    $estaCerca = ($diferenciaDias <= 5);
    $yaPaso = ($fechaPagoDateTime < $fechaActual && $diferenciaDias <= 5);
    $fichaje['pago_cerca'] = $estaCerca || $yaPaso;
  } else {
    $fichaje['pago_cerca'] = false;
  }

  $fichajesConPago[] = $fichaje;
}

// Limitar los fichajes a los 10 registros más recientes e invertir el orden
$fichajesConPago = array_slice($fichajesConPago, 0, 10);
$fichajesConPago = array_reverse($fichajesConPago);
?>

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
    <input type="text" id="busqueda_fichaje" placeholder="Buscar Fichaje" onkeyup="buscarFichaje()">
    <input type="text" id="busqueda_usuario" placeholder="Buscar Cliente">
  </div>
  <div class="tabla">
    <h2>Últimos fichajes</h2>
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
      <?php foreach ($fichajesConPago as $fichaje) { ?>
        <tr>
          <td><?php echo htmlspecialchars($fichaje['rfid']); ?></td>
          <td class="<?php echo $fichaje['pago_cerca'] ? 'cerca-de-vencer' : ''; ?>">
            <?php echo htmlspecialchars($fichaje['dni']); ?>
          </td>
          <td><?php echo htmlspecialchars($fichaje['alumno']); ?></td>
          <td><?php echo htmlspecialchars($fichaje['addmission_date']); ?></td>
          <td class="<?php echo $fichaje['pago_cerca'] ? 'cerca-de-vencer' : ''; ?>">
            <?php echo htmlspecialchars($fichaje['date_of_renovation']); ?>
          </td>
        </tr>
      <?php } ?>
      </tbody>
    </table>
  </div>

  <div class="botonera">
    <button onclick="window.location.href='cargar_usuario_view.php'" id="cargar_usuario">Agregar Cliente</button>
  </div>
  <script src="../public/js/busqueda_usuario.js"></script>
  <script src="../public/js/busqueda_fichaje.js"></script>
</body>
</html>
