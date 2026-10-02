<?php use App\Core\Config; ?>
<footer class="pie">
  <p>&copy; <?= date('Y') ?> <?= e(Config::get('app.nombre')) ?>. Todos los derechos reservados.</p>
  <p>Desarrollado por <span class="pie-autor">PC Fighter</span> · v<?= e(Config::get('app.version')) ?></p>
</footer>
