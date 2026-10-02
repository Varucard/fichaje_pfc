<?php
use App\Core\Session;

$iconos = ['exito' => 'fa-circle-check', 'error' => 'fa-circle-exclamation', 'aviso' => 'fa-circle-info'];
?>
<?php foreach (Session::tomarFlashes() as $flash): ?>
  <div class="flash flash-<?= e($flash['tipo']) ?>" role="alert">
    <i class="fas <?= e($iconos[$flash['tipo']] ?? 'fa-circle-info') ?>"></i>
    <span><?= e($flash['mensaje']) ?></span>
    <button type="button" class="flash-cerrar" aria-label="Cerrar" data-cerrar-flash>&times;</button>
  </div>
<?php endforeach; ?>
