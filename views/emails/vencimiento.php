<?php
/** @var array $usuario @var string $renovacion @var int $dias @var float $cuota @var App\Core\View $this */
?>
<p>¡Hola <?= e($usuario['user_name']) ?>!</p>
<p>Te recordamos que tu cuota vence <strong><?= $dias === 0 ? 'hoy' : ($dias === 1 ? 'mañana' : "en {$dias} días") ?></strong>.</p>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;border:1px solid #eee;border-radius:6px;">
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Vencimiento', 'valor' => fecha($renovacion)]) ?>
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Cuota mensual', 'valor' => dinero($cuota)]) ?>
</table>
<p>Podés abonarla en recepción antes de tu próxima clase para seguir entrenando sin interrupciones.</p>
<p>¡Te esperamos! 💪</p>
