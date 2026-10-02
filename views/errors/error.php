<?php
/**
 * @var int $status
 * @var string $mensaje
 */
?>
<div class="pagina-error">
  <img src="<?= asset('img/logo.png') ?>" alt="Palillo Fight Club" width="150" height="150">
  <h1>Error <?= (int) $status ?></h1>
  <p><?= e($mensaje) ?></p>
  <a class="boton" href="<?= url('/') ?>"><i class="fas fa-home"></i> Volver al inicio</a>
</div>
