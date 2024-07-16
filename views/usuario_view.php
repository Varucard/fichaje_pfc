<?php
require_once '../models/user_model.php';

$user = new User();

// Captura el DNI del usuario de la URL
$dni = isset($_GET['dni']) ? $_GET['dni'] : '';

// Busca los datos del usuario por DNI
$usuario = $user->getUserByDNI($dni);
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <link rel="shortcut icon" href="../public/img/ico_logo.png">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css">
  <link rel="stylesheet" href="../public/css/estilo.css">
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Detalle del Usuario</title>
</head>
<body>
  <div class="cabecera">
    <img src="../public/img/logo.png" alt="logo.png" width="100" height="100">
    <h1 style="margin-right: 50px;">Detalle del Usuario</h1>
    <button onclick="window.location.href='busqueda_usuario_view.php'">Volver</button>
  </div>
  <div class="detalle-usuario">
    <?php if ($usuario): ?>
      <h2><?php echo htmlspecialchars($usuario['name'] . ' ' . $usuario['surname']); ?></h2>
      <p>DNI: <?php echo htmlspecialchars($usuario['dni']); ?></p>
      <p>Email: <?php echo htmlspecialchars($usuario['email']); ?></p>
      <p>Teléfono: <?php echo htmlspecialchars($usuario['phone_number']); ?></p>
      <!-- Añade más campos según tus necesidades -->
    <?php else: ?>
      <p>No se encontraron datos para este usuario.</p>
    <?php endif; ?>
  </div>
</body>
</html>
