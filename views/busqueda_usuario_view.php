<?php
// Decodificar los resultados de la URL
$resultados = [];
if (isset($_GET['resultados'])) {
    $resultados = json_decode(urldecode($_GET['resultados']), true);
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
  <title>Resultados de Búsqueda - Palillo Fight Club</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Resultados de Búsqueda</h1>
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
          </tr>
        </thead>
        <tbody>
          <?php foreach ($resultados as $usuario) { ?>
            <tr>
              <td><?php echo htmlspecialchars($usuario['dni'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($usuario['name'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td><?php echo htmlspecialchars($usuario['surname'], ENT_QUOTES, 'UTF-8'); ?></td>
              <td>
                <button onclick="window.location.href='usuario_view.php?dni=<?php echo urlencode($usuario['dni']); ?>'">
                  <i class="fas fa-user"></i>
                </button>
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
</body>
</html>
