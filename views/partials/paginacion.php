<?php
/**
 * @var int $pagina
 * @var int $paginas
 * @var string $ruta
 * @var array $query Filtros a conservar en los enlaces
 */
if ($paginas <= 1) {
  return;
}
$enlace = fn (int $p) => url($ruta, array_filter($query) + ['pagina' => $p]);
?>
<nav class="paginacion" aria-label="Paginación">
  <?php if ($pagina > 1): ?>
    <a class="boton button_small" href="<?= e($enlace($pagina - 1)) ?>">&laquo; Anterior</a>
  <?php endif; ?>
  <span>Página <?= (int) $pagina ?> de <?= (int) $paginas ?></span>
  <?php if ($pagina < $paginas): ?>
    <a class="boton button_small" href="<?= e($enlace($pagina + 1)) ?>">Siguiente &raquo;</a>
  <?php endif; ?>
</nav>
