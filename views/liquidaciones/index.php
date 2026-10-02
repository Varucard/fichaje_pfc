<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var string $periodo AAAA-MM
 * @var array $filas
 * @var array $totales
 * @var string $resaltar DNI del profesor a resaltar
 */
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<form method="get" class="filtros">
  <label>Período <input type="month" name="periodo" value="<?= e($periodo) ?>" max="<?= date('Y-m') ?>"></label>
  <button type="submit"><i class="fas fa-calendar-alt"></i> Ver</button>
</form>

<div class="resumen">
  <div class="resumen-dato"><span>Total a liquidar</span><strong><?= e(dinero($totales['a_liquidar'])) ?></strong></div>
  <div class="resumen-dato"><span>Registrado</span><strong><?= e(dinero($totales['registrado'])) ?></strong></div>
  <div class="resumen-dato exito"><span>Pagado</span><strong><?= e(dinero($totales['pagado'])) ?></strong></div>
</div>

<p class="diminuto">
  Base = lo cobrado en el mes en las clases del profesor (dividido si la clase tiene varios profesores).
  Monto = base × porcentaje. Los pagos anteriores a la versión 3.1 no tienen reparto por clase y no se incluyen.
</p>

<div class="tabla">
  <?php if (empty($filas)): ?>
    <p>No hay profesores activos.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Profesor</th>
          <th>Clases</th>
          <th>Base</th>
          <th>%</th>
          <th>Monto</th>
          <th>Estado</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filas as $fila):
          $profesor = $fila['profesor'];
          $calculo = $fila['calculo'];
          $registrada = $fila['registrada'];
        ?>
          <tr class="<?= $resaltar !== '' && $resaltar === (string) $profesor['dni'] ? 'resaltada' : '' ?>">
            <td class="texto-izquierda">
              <a href="<?= url('/usuarios/' . $profesor['dni']) ?>"><?= e(trim($profesor['user_name'] . ' ' . $profesor['user_surname'])) ?></a>
            </td>
            <td class="texto-izquierda diminuto">
              <?php if (empty($calculo['detalle'])): ?>
                Sin clases
              <?php else: ?>
                <?php foreach ($calculo['detalle'] as $clase): ?>
                  <?= e($clase['clase']) ?>: <?= e(dinero($clase['cobrado'])) ?><?= $clase['profesores'] > 1 ? ' ÷ ' . (int) $clase['profesores'] : '' ?><br>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
            <td><?= e(dinero($calculo['base'])) ?></td>
            <td>
              <?php if ($registrada): ?>
                <?= e($calculo['porcentaje'] + 0) ?>%
              <?php else: ?>
                <form action="<?= url('/liquidaciones/profesores/' . $profesor['id_user'] . '/porcentaje') ?>" method="post" class="form-porcentaje">
                  <?= csrf_field() ?>
                  <input type="hidden" name="periodo" value="<?= e($periodo) ?>">
                  <input type="number" name="porcentaje" min="0" max="100" step="0.5"
                    value="<?= $fila['porcentaje_propio'] ? e($calculo['porcentaje'] + 0) : '' ?>"
                    placeholder="<?= e($calculo['porcentaje'] + 0) ?>" title="Vacío = porcentaje por defecto">
                  <button type="submit" class="button_small" title="Guardar porcentaje"><i class="fas fa-check"></i></button>
                </form>
              <?php endif; ?>
            </td>
            <td><strong><?= e(dinero($calculo['monto'])) ?></strong></td>
            <td class="celda-acciones">
              <?php if (!$registrada): ?>
                <form action="<?= url('/liquidaciones') ?>" method="post"
                  data-confirmar="¿Registrar la liquidación de <?= e($profesor['user_name']) ?> por <?= e(dinero($calculo['monto'])) ?>?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="id_profesor" value="<?= e($profesor['id_user']) ?>">
                  <input type="hidden" name="periodo" value="<?= e($periodo) ?>">
                  <button type="submit" class="button_small"><i class="fas fa-file-signature"></i> Registrar</button>
                </form>
              <?php elseif (!$registrada['pagada']): ?>
                <span class="etiqueta etiqueta-warning">Registrada</span>
                <form action="<?= url('/liquidaciones/' . $registrada['id'] . '/pagar') ?>" method="post"
                  data-confirmar="¿Marcar como pagada la liquidación de <?= e($profesor['user_name']) ?>?">
                  <?= csrf_field() ?>
                  <button type="submit" class="button_small"><i class="fas fa-money-bill-wave"></i> Pagar</button>
                </form>
                <form action="<?= url('/liquidaciones/' . $registrada['id'] . '/anular') ?>" method="post"
                  data-confirmar="¿Anular la liquidación? Se podrá volver a calcular.">
                  <?= csrf_field() ?>
                  <button type="submit" class="button_small peligro"><i class="fas fa-undo"></i> Anular</button>
                </form>
              <?php else: ?>
                <span class="etiqueta etiqueta-exito">Pagada <?= e(fecha($registrada['fecha_pago'])) ?></span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/navegacion') ?>
