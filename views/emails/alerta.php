<?php
/** Alerta del sistema para el administrador. @var string $titulo_alerta @var string $mensaje @var string|null $detalle */
?>
<p style="font-size:18px;font-weight:bold;color:#e5484d;margin:0 0 12px;">⚠️ <?= e($titulo_alerta) ?></p>
<p><?= e($mensaje) ?></p>
<?php if (!empty($detalle)): ?>
  <pre style="background:#f7f7f9;border:1px solid #eee;border-radius:6px;padding:10px;font-size:12px;white-space:pre-wrap;"><?= e($detalle) ?></pre>
<?php endif; ?>
<p style="font-size:12px;color:#777;">Generado automáticamente el <?= date('d-m-Y H:i') ?>. Más detalle en el panel → Logs.</p>
