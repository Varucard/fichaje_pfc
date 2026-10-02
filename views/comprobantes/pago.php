<?php
/**
 * Comprobante de pago para PDF (Dompdf). Sin validez fiscal.
 * @var array $pago
 * @var array $usuario
 * @var array $detalle
 * @var string $numero
 * @var array $gimnasio
 * @var string|null $logo
 */
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <style>
    body { font-family: 'DejaVu Sans', sans-serif; font-size: 11px; color: #222; margin: 0; }
    .cabecera { border-bottom: 2px solid #111132; padding-bottom: 10px; margin-bottom: 14px; }
    .cabecera img { width: 60px; float: left; margin-right: 12px; }
    .cabecera h1 { font-size: 18px; margin: 0; color: #111132; }
    .cabecera p { margin: 2px 0; color: #555; }
    .titulo { clear: both; text-align: right; }
    .titulo h2 { margin: 0; font-size: 15px; }
    table { width: 100%; border-collapse: collapse; margin-top: 10px; }
    th, td { padding: 6px 8px; border-bottom: 1px solid #ddd; text-align: left; }
    th { background: #f2f3f5; }
    .num { text-align: right; }
    .total td { font-weight: bold; font-size: 13px; border-top: 2px solid #111132; }
    .nota { margin-top: 18px; font-size: 9px; color: #777; text-align: center; }
  </style>
</head>
<body>
  <div class="cabecera">
    <?php if ($logo): ?><img src="<?= $logo ?>" alt=""><?php endif; ?>
    <h1><?= e($gimnasio['nombre']) ?></h1>
    <?php if ($gimnasio['direccion']): ?><p><?= e($gimnasio['direccion']) ?></p><?php endif; ?>
    <?php if ($gimnasio['telefono']): ?><p><?= e($gimnasio['telefono']) ?></p><?php endif; ?>
    <div class="titulo">
      <h2>COMPROBANTE DE PAGO</h2>
      <p>N° <?= e($numero) ?></p>
    </div>
  </div>

  <table>
    <tr><th>Cliente</th><td><?= e(trim($usuario['user_name'] . ' ' . $usuario['user_surname'])) ?></td></tr>
    <tr><th>DNI</th><td><?= e($usuario['dni']) ?></td></tr>
    <tr><th>Fecha de pago</th><td><?= e(fecha($pago['discharge_date'])) ?></td></tr>
    <tr><th>Período cubierto</th><td>Del <?= e(fecha($pago['discharge_date'])) ?> al <?= e(fecha($pago['date_of_renovation'])) ?></td></tr>
  </table>

  <table>
    <thead><tr><th>Concepto</th><th class="num">Precio</th><th class="num">Importe</th></tr></thead>
    <tbody>
      <?php if ($detalle): ?>
        <?php foreach ($detalle as $linea): ?>
          <tr>
            <td>Cuota mensual — <?= e($linea['nombre_clase']) ?></td>
            <td class="num"><?= e(dinero($linea['precio_clase'])) ?></td>
            <td class="num"><?= e(dinero($linea['monto'])) ?></td>
          </tr>
        <?php endforeach; ?>
      <?php else: ?>
        <tr><td>Cuota mensual</td><td class="num">-</td><td class="num"><?= $pago['monto'] !== null ? e(dinero($pago['monto'])) : '-' ?></td></tr>
      <?php endif; ?>
    </tbody>
    <tfoot>
      <tr class="total"><td colspan="2">TOTAL ABONADO</td><td class="num"><?= $pago['monto'] !== null ? e(dinero($pago['monto'])) : '-' ?></td></tr>
      <?php if ($pago['monto_cuota'] !== null && (float) $pago['monto'] < (float) $pago['monto_cuota']): ?>
        <tr><td colspan="2">Saldo pendiente</td><td class="num"><?= e(dinero((float) $pago['monto_cuota'] - (float) $pago['monto'])) ?></td></tr>
      <?php endif; ?>
    </tfoot>
  </table>

  <p class="nota">Documento no válido como factura. Emitido el <?= date('d-m-Y H:i') ?>.</p>
</body>
</html>
