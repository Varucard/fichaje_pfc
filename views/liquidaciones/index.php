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
  <a class="boton" href="<?= url('/exportar/liquidaciones.csv', ['periodo' => $periodo]) ?>"><i class="fas fa-file-excel"></i> Exportar registradas</a>
</form>

<div class="resumen">
  <div class="resumen-dato"><span>Total a liquidar</span><strong><?= e(dinero($totales['a_liquidar'])) ?></strong></div>
  <div class="resumen-dato"><span>Registrado</span><strong><?= e(dinero($totales['registrado'])) ?></strong></div>
  <div class="resumen-dato exito"><span>Pagado</span><strong><?= e(dinero($totales['pagado'])) ?></strong></div>
</div>

<p class="diminuto">
  <strong>% de lo cobrado:</strong> base = lo cobrado en el mes en sus clases (los pagos de varios meses se reparten entre esos meses).
  <strong>Por asistencia:</strong> base = ingresos de alumnos a sus clases en el mes (según la clase de cada fichada).
  Si una clase tiene varios profesores, su base se divide entre ellos.
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
          <th>Modo</th>
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
                  <?= e($clase['clase']) ?>:
                  <?= isset($clase['asistencias']) ? (int) $clase['asistencias'] . ' asist.' : e(dinero($clase['cobrado'])) ?><?= $clase['profesores'] > 1 ? ' ÷ ' . (int) $clase['profesores'] : '' ?><br>
                <?php endforeach; ?>
              <?php endif; ?>
            </td>
            <td><?= $calculo['modo'] === 'asistencia' ? e($calculo['base'] + 0) . ' asist.' : e(dinero($calculo['base'])) ?></td>
            <td>
              <?php if ($registrada): ?>
                <?= $calculo['modo'] === 'asistencia' ? e(dinero($calculo['monto_por_asistencia'])) . ' / asist.' : e($calculo['porcentaje'] + 0) . '%' ?>
              <?php else:
                $esAsistencia = $calculo['modo'] === 'asistencia';
                $valor = $esAsistencia
                  ? (string) ($profesor['monto_por_asistencia'] ?? '')
                  : ($fila['porcentaje_propio'] ? (string) ($calculo['porcentaje'] + 0) : '');
              ?>
                <form action="<?= url('/liquidaciones/profesores/' . $profesor['id_user'] . '/configuracion') ?>" method="post" class="form-porcentaje">
                  <?= csrf_field() ?>
                  <input type="hidden" name="periodo" value="<?= e($periodo) ?>">
                  <select name="modo" aria-label="Modo de liquidación">
                    <option value="porcentaje" <?= $esAsistencia ? '' : 'selected' ?>>% cobrado</option>
                    <option value="asistencia" <?= $esAsistencia ? 'selected' : '' ?>>$ por asistencia</option>
                  </select>
                  <input type="number" name="valor" min="0" step="0.01" value="<?= e($valor) ?>"
                    placeholder="<?= $esAsistencia ? '$' : e($fila['porcentaje_defecto'] + 0) . '%' ?>"
                    title="Porcentaje (vacío = por defecto) o monto por asistencia">
                  <button type="submit" class="button_small" title="Guardar"><i class="fas fa-check"></i></button>
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
