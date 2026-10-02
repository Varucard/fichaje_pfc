<?php
/**
 * Reporte de caja: cuotas + ventas por mes (barras apiladas) y detalle de un mes.
 * @var App\Core\View $this
 * @var string $titulo
 * @var int $anio
 * @var array $meses
 * @var array $totales
 * @var float $maximo
 * @var int $mes_detalle
 * @var array $por_clase
 * @var float $sin_clase
 * @var array $por_producto
 */
use App\Services\ReporteService;

$this->script('grafico-caja');
$variacion = fn (?float $v) => $v === null ? '—' : ($v > 0 ? '+' : '') . number_format($v, 1, ',', '.') . ' %';
$alto = fn (float $valor) => $maximo > 0 ? round($valor / $maximo * 100, 2) : 0;
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<form method="get" class="filtros">
  <label>Año <input type="number" name="anio" value="<?= (int) $anio ?>" min="2000" max="<?= (int) date('Y') + 1 ?>"></label>
  <button type="submit"><i class="fas fa-calendar-alt"></i> Ver</button>
  <a class="boton" href="<?= url('/exportar/caja.csv', ['anio' => $anio]) ?>"><i class="fas fa-file-excel"></i> Exportar año</a>
</form>

<div class="resumen">
  <div class="resumen-dato"><span>Cuotas <?= (int) $anio ?> (<?= (int) $totales['pagos'] ?> pagos)</span><strong><?= e(dinero($totales['cuotas'])) ?></strong></div>
  <div class="resumen-dato"><span>Ventas de productos</span><strong><?= e(dinero($totales['ventas'])) ?></strong></div>
  <div class="resumen-dato exito"><span>Total · meses cerrados vs. <?= (int) $anio - 1 ?>: <?= e($variacion($totales['variacion'])) ?></span><strong><?= e(dinero($totales['total'])) ?></strong></div>
</div>

<figure class="grafico-caja" aria-labelledby="titulo-grafico">
  <figcaption id="titulo-grafico">
    Ingresos por mes <?= (int) $anio ?>
    <span class="leyenda">
      <span><i class="muestra serie-cuotas"></i> Cuotas</span>
      <span><i class="muestra serie-ventas"></i> Ventas</span>
    </span>
  </figcaption>
  <div class="grafico-area">
    <div class="grafico-escala" aria-hidden="true">
      <span><?= e(dinero($maximo)) ?></span>
      <span><?= e(dinero($maximo / 2)) ?></span>
      <span>$0</span>
    </div>
    <div class="grafico-barras">
      <?php foreach ($meses as $m): ?>
        <a class="columna <?= $m['futuro'] ? 'futuro' : '' ?> <?= $m['mes'] === $mes_detalle ? 'elegida' : '' ?>"
          href="<?= url('/reportes/caja', ['anio' => $anio, 'mes' => $m['mes']]) ?>"
          data-tooltip="<?= e($m['nombre'] . ' ' . $anio . "\nCuotas: " . dinero($m['cuotas']) . "\nVentas: " . dinero($m['ventas']) . "\nTotal: " . dinero($m['total']) . ($m['variacion'] !== null ? "\nvs. año anterior: " . $variacion($m['variacion']) : '')) ?>"
          aria-label="<?= e($m['nombre'] . ': total ' . dinero($m['total'])) ?>">
          <span class="pila">
            <?php if ($m['ventas'] > 0): ?><span class="segmento serie-ventas" style="height: <?= $alto($m['ventas']) ?>%"></span><?php endif; ?>
            <?php if ($m['cuotas'] > 0): ?><span class="segmento serie-cuotas" style="height: <?= $alto($m['cuotas']) ?>%"></span><?php endif; ?>
          </span>
          <span class="etiqueta-mes"><?= e($m['nombre']) ?></span>
        </a>
      <?php endforeach; ?>
    </div>
  </div>
  <div class="grafico-tooltip" role="tooltip" hidden></div>
</figure>

<details class="bloque">
  <summary><strong>Ver como tabla</strong></summary>
  <table>
    <thead><tr><th>Mes</th><th>Pagos</th><th>Cuotas</th><th>Ventas</th><th>Total</th><th>vs. <?= (int) $anio - 1 ?></th></tr></thead>
    <tbody>
      <?php foreach ($meses as $m): ?>
        <tr class="<?= $m['futuro'] ? 'inactivo' : '' ?>">
          <td><?= e($m['nombre']) ?></td>
          <td><?= (int) $m['pagos'] ?></td>
          <td><?= e(dinero($m['cuotas'])) ?></td>
          <td><?= e(dinero($m['ventas'])) ?></td>
          <td><strong><?= e(dinero($m['total'])) ?></strong></td>
          <td class="diminuto"><?= $m['en_curso'] ? 'en curso' : e($variacion($m['variacion'])) ?></td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</details>

<h2>Detalle de <?= e(ReporteService::MESES[$mes_detalle]) ?> <?= (int) $anio ?></h2>
<div class="tabla-container">
  <div class="tabla">
    <h3>Cuotas por clase</h3>
    <?php if (empty($por_clase) && $sin_clase <= 0): ?>
      <p>Sin cobros en el mes.</p>
    <?php else: ?>
      <table>
        <thead><tr><th>Clase</th><th>Pagos</th><th>Cobrado</th></tr></thead>
        <tbody>
          <?php foreach ($por_clase as $fila): ?>
            <tr><td class="texto-izquierda"><?= e($fila['clase']) ?></td><td><?= (int) $fila['pagos'] ?></td><td><?= e(dinero($fila['total'])) ?></td></tr>
          <?php endforeach; ?>
          <?php if ($sin_clase > 0): ?>
            <tr><td class="texto-izquierda diminuto">Sin detalle por clase</td><td></td><td><?= e(dinero($sin_clase)) ?></td></tr>
          <?php endif; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
  <div class="tabla">
    <h3>Ventas por producto</h3>
    <?php if (empty($por_producto)): ?>
      <p>Sin ventas en el mes.</p>
    <?php else: ?>
      <table>
        <thead><tr><th>Producto</th><th>Unidades</th><th>Total</th></tr></thead>
        <tbody>
          <?php foreach ($por_producto as $fila): ?>
            <tr><td class="texto-izquierda"><?= e($fila['producto']) ?></td><td><?= (int) $fila['unidades'] ?></td><td><?= e(dinero($fila['total'])) ?></td></tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  </div>
</div>

<div class="acciones">
  <?php $desde = sprintf('%04d-%02d-01', $anio, $mes_detalle); ?>
  <a class="boton" href="<?= url('/exportar/pagos.csv', ['desde' => $desde, 'hasta' => date('Y-m-t', strtotime($desde))]) ?>">
    <i class="fas fa-file-excel"></i> Exportar pagos de <?= e(ReporteService::MESES[$mes_detalle]) ?>
  </a>
</div>

<p class="diminuto">Criterio de caja: cada cobro suma en el mes en que se cobró. Un pago de varios meses suma completo en su mes (la liquidación de profesores, en cambio, lo reparte entre los meses que cubre). Los pagos anteriores a la v3.1 no tienen monto registrado y no suman.</p>

<?= $this->renderParcial('partials/navegacion') ?>
