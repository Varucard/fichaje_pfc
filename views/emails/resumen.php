<?php
/**
 * Resumen semanal para el administrador.
 * @var App\Core\View $this
 * @var array $resumen
 */
$celda = 'padding:6px 10px;border-bottom:1px solid #eee;';
?>
<p>Resumen de la semana del <strong><?= e(fecha($resumen['desde'])) ?></strong> al <strong><?= e(fecha($resumen['hasta'])) ?></strong>.</p>

<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:16px 0;border:1px solid #eee;border-radius:6px;">
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Ingresos (fichadas)', 'valor' => (string) $resumen['fichadas']]) ?>
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Pagos registrados', 'valor' => $resumen['pagos']['cantidad'] . ' · ' . dinero($resumen['pagos']['total'])]) ?>
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Altas de clientes', 'valor' => (string) $resumen['altas']]) ?>
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Ventas de productos', 'valor' => dinero($resumen['ventas']['total'])]) ?>
  <?= $this->renderParcial('emails/_dato', ['etiqueta' => 'Alumnos con deuda', 'valor' => count($resumen['deudores']) . ' · ' . dinero($resumen['deuda_total']), 'alerta' => $resumen['deuda_total'] > 0]) ?>
</table>

<?php if ($resumen['deudores']): ?>
  <p><strong>Mayores deudas</strong></p>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
    <?php foreach (array_slice($resumen['deudores'], 0, 10) as $deudor): ?>
      <tr>
        <td style="<?= $celda ?>"><?= e(trim($deudor['user_name'] . ' ' . $deudor['user_surname'])) ?> (DNI <?= e($deudor['dni']) ?>)</td>
        <td style="<?= $celda ?>text-align:right;color:#e5484d;"><?= e(dinero($deudor['deuda']['total'])) ?></td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<?php if ($resumen['stock_bajo']): ?>
  <p><strong>Productos para reponer</strong></p>
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
    <?php foreach ($resumen['stock_bajo'] as $producto): ?>
      <tr>
        <td style="<?= $celda ?>"><?= e($producto['nombre']) ?></td>
        <td style="<?= $celda ?>text-align:right;">Stock <?= (int) $producto['stock'] ?> (mínimo <?= (int) $producto['stock_minimo'] ?>)</td>
      </tr>
    <?php endforeach; ?>
  </table>
<?php endif; ?>

<?php if ($resumen['liquidaciones_pendientes']): ?>
  <p><strong>Liquidaciones registradas sin pagar:</strong> <?= (int) $resumen['liquidaciones_pendientes']['cantidad'] ?> por <?= e(dinero($resumen['liquidaciones_pendientes']['total'])) ?>.</p>
<?php endif; ?>

<p><a href="<?= e(url_absoluta('/dashboard')) ?>" style="display:inline-block;background:#111132;color:#fff;padding:10px 18px;border-radius:6px;text-decoration:none;">Abrir el panel</a></p>
