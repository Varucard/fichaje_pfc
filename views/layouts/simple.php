<?php
/**
 * Layout sin navegación (login y páginas de error).
 * @var App\Core\View $this
 * @var string $contenido
 */
use App\Core\Config;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($titulo ?? '') ?> | <?= e(Config::get('app.nombre')) ?></title>
  <link rel="icon" href="<?= asset('img/ico_logo.png') ?>">
  <link rel="stylesheet" href="<?= asset('css/water.css') ?>">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" referrerpolicy="no-referrer">
  <link rel="stylesheet" href="<?= asset('css/estilos.css') ?>">
</head>
<body>
  <?= $this->renderParcial('partials/flash') ?>

  <main>
    <?= $contenido ?>
  </main>

  <?= $this->renderParcial('partials/footer') ?>
</body>
</html>
