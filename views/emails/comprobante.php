<?php
/** @var array $usuario @var array $pago @var string $numero @var App\Core\View $this */
?>
<p>¡Hola <?= e($usuario['user_name']) ?>!</p>
<p>Registramos tu pago. ¡Gracias! Te adjuntamos el comprobante en PDF.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;border:1px solid #eee;border-radius:6px;">
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Comprobante N°', 'valor' => $numero]) ?>
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Fecha de pago', 'valor' => fecha($pago['discharge_date'])]) ?>
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Importe', 'valor' => $pago['monto'] !== null ? dinero($pago['monto']) : '-']) ?>
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Válido hasta', 'valor' => fecha($pago['date_of_renovation'])]) ?>
</table>
<p style="font-size:12px;color:#777;">Documento no válido como factura.</p>
