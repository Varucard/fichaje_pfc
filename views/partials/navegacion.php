<?php
/**
 * Botones "Volver" e "Inicio" al pie de cada pantalla.
 * @var string|null $volver Ruta a la que vuelve si no hay historial.
 */
?>
<nav class="botonera">
  <button type="button" data-volver="<?= e(url($volver ?? '/dashboard')) ?>">
    <i class="fas fa-arrow-left"></i> Volver
  </button>
  <a class="boton" href="<?= url('/dashboard') ?>">
    <i class="fas fa-home"></i> Inicio
  </a>
</nav>
