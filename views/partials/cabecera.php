<?php
/**
 * Cabecera de página con logo, título y buscador opcional.
 * @var string $titulo
 * @var string|null $buscar      Tipo de búsqueda: usuarios | fichajes | clases
 * @var string|null $placeholder
 * @var string|null $termino
 */
?>
<header class="cabecera">
  <a href="<?= url('/dashboard') ?>"><img src="<?= asset('img/logo.png') ?>" alt="Palillo Fight Club" width="100" height="100"></a>
  <h1><?= e($titulo) ?></h1>
  <?php if (!empty($buscar)): ?>
    <input type="search" class="cabecera-buscador" data-buscar="<?= e($buscar) ?>"
      placeholder="<?= e($placeholder ?? 'Buscar') ?>" value="<?= e($termino ?? '') ?>" aria-label="<?= e($placeholder ?? 'Buscar') ?>">
  <?php endif; ?>
</header>
