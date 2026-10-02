<?php
/**
 * Layout principal del panel.
 * @var App\Core\View $this
 * @var string $contenido
 * @var string $titulo
 */
use App\Core\Config;
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="base-url" content="<?= e(rtrim(url('/'), '/')) ?>">
  <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
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

  <div id="avisos" class="avisos" aria-live="polite"></div>

  <?= $this->renderParcial('partials/footer') ?>

  <script src="<?= asset('js/app.js') ?>"></script>
  <?php foreach ($this->scripts() as $script): ?>
    <script src="<?= asset("js/{$script}.js") ?>"></script>
  <?php endforeach; ?>
</body>
</html>
