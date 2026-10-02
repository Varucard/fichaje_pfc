<?php
/** @var array $usuario @var array $deuda @var App\Core\View $this */
?>
<p>¡Hola <?= e($usuario['user_name']) ?>!</p>
<p>Según nuestros registros tenés un saldo pendiente con el gimnasio:</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;border:1px solid #eee;border-radius:6px;">
  <?php if ($deuda['renovacion']): ?>
    <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Venció el', 'valor' => fecha($deuda['renovacion'])]) ?>
  <?php endif; ?>
  <?php if ($deuda['meses'] > 0): ?>
    <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Cuotas vencidas', 'valor' => $deuda['meses'] . ' × ' . dinero($deuda['cuota'])]) ?>
  <?php endif; ?>
  <?php if ($deuda['saldo_pagos'] > 0): ?>
    <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Saldo de pagos parciales', 'valor' => dinero($deuda['saldo_pagos'])]) ?>
  <?php endif; ?>
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Total adeudado', 'valor' => dinero($deuda['total']), 'alerta' => true]) ?>
</table>
<p>Recordá que con la cuota vencida el lector de la entrada no habilita el ingreso. Acercate a recepción para regularizarlo.</p>
<p>Si ya pagaste, ignorá este mensaje. ¡Gracias!</p>
