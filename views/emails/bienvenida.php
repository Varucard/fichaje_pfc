<?php
/** @var array $usuario @var array $clases @var float $cuota @var array $gimnasio */
?>
<p>¡Hola <?= e($usuario['user_name']) ?>, bienvenido/a a <?= e($gimnasio['nombre']) ?>! 🥋</p>
<p>Ya quedaste registrado/a. Estos son tus datos:</p>
<ul>
  <?php if ($clases): ?>
    <li><strong>Clases:</strong> <?= e(implode(', ', array_column($clases, 'name_class'))) ?></li>
    <li><strong>Cuota mensual:</strong> <?= e(dinero($cuota)) ?></li>
  <?php endif; ?>
  <li><strong>Ingreso:</strong> pasá tu llavero por el lector de la entrada. Si todavía no tenés uno, pedilo en recepción.</li>
</ul>
<p>Te vamos a avisar por este medio antes de que venza tu cuota.</p>
<p>¡Nos vemos en el entrenamiento! 💪</p>
