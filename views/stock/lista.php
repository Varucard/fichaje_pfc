<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $productos
 * @var int $bajo_minimo
 * @var array{cantidad: int, total: float} $ventas_mes
 */
use App\Services\StockService;
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<div class="resumen">
  <div class="resumen-dato"><span>Productos activos</span><strong><?= count(array_filter($productos, fn ($p) => $p['activo'])) ?></strong></div>
  <div class="resumen-dato <?= $bajo_minimo ? 'peligro' : 'exito' ?>"><span>Con stock bajo</span><strong><?= (int) $bajo_minimo ?></strong></div>
  <div class="resumen-dato exito"><span>Ventas del mes (<?= (int) $ventas_mes['cantidad'] ?> u.)</span><strong><?= e(dinero($ventas_mes['total'])) ?></strong></div>
</div>

<div class="acciones">
  <a class="boton" href="<?= url('/stock/nuevo') ?>"><i class="fas fa-plus"></i> Nuevo producto</a>
</div>

<div class="tabla">
  <?php if (empty($productos)): ?>
    <p>Todavía no hay productos cargados.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Producto</th>
          <th>Precio</th>
          <th>Stock</th>
          <th>Mínimo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($productos as $producto): ?>
          <tr class="<?= $producto['activo'] ? '' : 'inactivo' ?>">
            <td class="texto-izquierda">
              <?= e($producto['nombre']) ?>
              <?php if (!$producto['activo']): ?><span class="diminuto">(desactivado)</span><?php endif; ?>
            </td>
            <td><?= e(dinero($producto['precio'])) ?></td>
            <td class="<?= StockService::bajoMinimo($producto) ? 'destacado' : '' ?>">
              <strong><?= (int) $producto['stock'] ?></strong>
              <?php if (StockService::bajoMinimo($producto)): ?><br><span class="diminuto">Reponer</span><?php endif; ?>
            </td>
            <td class="diminuto"><?= (int) $producto['stock_minimo'] ?></td>
            <td class="celda-acciones">
              <a class="boton button_small" href="<?= url('/stock/' . $producto['id']) ?>"><i class="fas fa-eye"></i> Ver / Mover</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/navegacion') ?>
