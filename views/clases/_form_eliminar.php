<?php
/**
 * Botón para eliminar una clase. Si tiene matriculaciones, la confirmación lo advierte
 * y se envía la marca para eliminarlas junto con la clase.
 * @var array $clase       Debe incluir 'matriculaciones' (cantidad)
 * @var bool|null $chico
 */
$matriculaciones = (int) ($clase['matriculaciones'] ?? 0);
$mensaje = $matriculaciones > 0
  ? "La clase {$clase['name_class']} tiene {$matriculaciones} alumno(s)/profesor(es) matriculados. ¿Eliminar la clase y sus matriculaciones?"
  : "¿Eliminar la clase {$clase['name_class']}?";
?>
<form action="<?= url('/clases/' . $clase['id_class'] . '/eliminar') ?>" method="post" data-confirmar="<?= e($mensaje) ?>">
  <?= csrf_field() ?>
  <?php if ($matriculaciones > 0): ?>
    <input type="hidden" name="incluir_matriculaciones" value="1">
  <?php endif; ?>
  <button type="submit" class="peligro <?= !empty($chico) ? 'button_small' : '' ?>"><i class="fas fa-trash"></i> Eliminar<?= empty($chico) ? ' Clase' : '' ?></button>
</form>
