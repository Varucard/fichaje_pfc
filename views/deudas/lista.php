<?php
/**
 * Alumnos activos con deuda.
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $deudores
 * @var float $total
 */
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<div class="resumen">
  <div class="resumen-dato"><span>Alumnos con deuda</span><strong><?= count($deudores) ?></strong></div>
  <div class="resumen-dato peligro"><span>Deuda total</span><strong><?= e(dinero($total)) ?></strong></div>
</div>

<p class="diminuto">
  Deuda = meses vencidos × cuota mensual (suma de sus clases) + saldos de pagos parciales.
</p>

<div class="tabla">
  <?php if (empty($deudores)): ?>
    <p>No hay alumnos con deuda. 🎉</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>DNI</th>
          <th>Alumno</th>
          <th>Venció</th>
          <th>Meses</th>
          <th>Cuota</th>
          <th>Saldo parcial</th>
          <th>Deuda</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($deudores as $alumno): $d = $alumno['deuda']; ?>
          <tr>
            <td><?= e($alumno['dni']) ?></td>
            <td class="texto-izquierda"><?= e(trim($alumno['user_name'] . ' ' . $alumno['user_surname'])) ?></td>
            <td class="diminuto"><?= e($d['renovacion'] ? fecha($d['renovacion']) : 'Nunca pagó') ?></td>
            <td><?= (int) $d['meses'] ?></td>
            <td><?= e(dinero($d['cuota'])) ?></td>
            <td><?= $d['saldo_pagos'] > 0 ? e(dinero($d['saldo_pagos'])) : '-' ?></td>
            <td class="destacado"><strong><?= e(dinero($d['total'])) ?></strong></td>
            <td><a class="boton button_small" href="<?= url('/usuarios/' . $alumno['dni']) ?>"><i class="fas fa-wallet"></i> Cobrar</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/navegacion') ?>
