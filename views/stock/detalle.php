<?php
/**
 * @var App\Core\View $this
 * @var array $producto
 * @var array $movimientos
 * @var array<string, string> $tipos
 */
use App\Services\StockService;

$ruta = '/stock/' . $producto['id'];
$activo = (bool) $producto['activo'];
?>
<header class="cabecera">
  <img src="<?= asset('img/logo.png') ?>" alt="Palillo Fight Club" width="100" height="100">
  <h1><?= e($producto['nombre']) ?></h1>
  <div class="cabecera-acciones">
    <a class="boton" href="<?= url('/stock') ?>"><i class="fas fa-undo-alt"></i> Volver</a>
    <form action="<?= url($ruta . '/activo') ?>" method="post"
      data-confirmar="¿<?= $activo ? 'Desactivar' : 'Reactivar' ?> el producto?">
      <?= csrf_field() ?>
      <input type="hidden" name="activo" value="<?= $activo ? '0' : '1' ?>">
      <button type="submit" class="<?= $activo ? 'peligro' : '' ?>">
        <i class="fas <?= $activo ? 'fa-ban' : 'fa-check' ?>"></i> <?= $activo ? 'Desactivar' : 'Reactivar' ?>
      </button>
    </form>
  </div>
</header>

<section class="detalle">
  <div class="resumen">
    <div class="resumen-dato <?= StockService::bajoMinimo($producto) ? 'peligro' : 'exito' ?>">
      <span>Stock actual (mínimo <?= (int) $producto['stock_minimo'] ?>)</span><strong><?= (int) $producto['stock'] ?></strong>
    </div>
    <div class="resumen-dato"><span>Precio de venta</span><strong><?= e(dinero($producto['precio'])) ?></strong></div>
  </div>

  <?php if ($activo): $mov = old('tipo_movimiento'); ?>
    <h2>Registrar movimiento</h2>
    <div class="movimientos">
      <form action="<?= url($ruta . '/movimientos') ?>" method="post" class="tarjeta-movimiento">
        <?= csrf_field() ?>
        <input type="hidden" name="tipo" value="venta">
        <h3><i class="fas fa-cash-register"></i> Venta</h3>
        <label>Cantidad <input type="number" name="cantidad" min="1" step="1" value="<?= e($mov === 'venta' ? old('cantidad', '1') : '1') ?>" required></label>
        <label>Precio unitario $ <input type="number" name="precio" min="0" step="0.01" value="<?= e($mov === 'venta' ? old('precio', $producto['precio']) : $producto['precio']) ?>"></label>
        <label>DNI del cliente (opcional) <input type="text" name="dni" inputmode="numeric" pattern="\d{7,8}" value="<?= e($mov === 'venta' ? old('dni') : '') ?>"></label>
        <label>Observación <input type="text" name="observacion" maxlength="255" value="<?= e($mov === 'venta' ? old('observacion') : '') ?>"></label>
        <button type="submit">Registrar venta</button>
      </form>

      <form action="<?= url($ruta . '/movimientos') ?>" method="post" class="tarjeta-movimiento">
        <?= csrf_field() ?>
        <input type="hidden" name="tipo" value="entrada">
        <h3><i class="fas fa-truck-loading"></i> Entrada</h3>
        <label>Cantidad <input type="number" name="cantidad" min="1" step="1" required></label>
        <label>Costo unitario $ (opcional) <input type="number" name="precio" min="0" step="0.01"></label>
        <label>Observación <input type="text" name="observacion" maxlength="255" placeholder="Proveedor, factura…"></label>
        <button type="submit">Registrar entrada</button>
      </form>

      <form action="<?= url($ruta . '/movimientos') ?>" method="post" class="tarjeta-movimiento"
        data-confirmar="¿Ajustar el stock al valor contado?">
        <?= csrf_field() ?>
        <input type="hidden" name="tipo" value="ajuste">
        <h3><i class="fas fa-clipboard-list"></i> Ajuste de inventario</h3>
        <label>Stock real contado <input type="number" name="stock_real" min="0" step="1" value="<?= (int) $producto['stock'] ?>" required></label>
        <label>Motivo <input type="text" name="observacion" maxlength="255" placeholder="Inventario, rotura…" required></label>
        <button type="submit">Ajustar</button>
      </form>
    </div>
  <?php endif; ?>

  <hr>
  <h2>Datos del producto</h2>
  <form action="<?= url($ruta . '/actualizar') ?>" method="post" class="form-container">
    <?= csrf_field() ?>
    <?= $this->renderParcial('stock/_campos_producto', ['producto' => [
      'nombre' => old('nombre', $producto['nombre']),
      'descripcion' => old('descripcion', $producto['descripcion']),
      'precio' => old('precio', $producto['precio']),
      'stock_minimo' => old('stock_minimo', $producto['stock_minimo']),
    ]]) ?>
    <div class="form-botones">
      <button type="submit"><i class="fas fa-sync-alt"></i> Actualizar producto</button>
    </div>
  </form>

  <hr>
  <h2>Movimientos</h2>
  <?php if (empty($movimientos)): ?>
    <p>Sin movimientos.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr><th>Fecha</th><th>Tipo</th><th>Cantidad</th><th>Stock</th><th>Importe</th><th>Detalle</th></tr>
      </thead>
      <tbody>
        <?php foreach ($movimientos as $mov): ?>
          <tr>
            <td class="diminuto"><?= e(fecha_hora($mov['fecha'])) ?></td>
            <td><?= e($tipos[$mov['tipo']] ?? $mov['tipo']) ?></td>
            <td class="<?= $mov['cantidad'] < 0 ? 'destacado' : '' ?>"><?= $mov['cantidad'] > 0 ? '+' : '' ?><?= (int) $mov['cantidad'] ?></td>
            <td><?= (int) $mov['stock_resultante'] ?></td>
            <td><?= $mov['total'] !== null ? e(dinero($mov['total'])) : '-' ?></td>
            <td class="texto-izquierda diminuto">
              <?php if ($mov['dni_cliente']): ?>
                <a href="<?= url('/usuarios/' . $mov['dni_cliente']) ?>"><?= e($mov['cliente']) ?></a><br>
              <?php endif; ?>
              <?= e($mov['observacion']) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</section>

<?= $this->renderParcial('partials/navegacion', ['volver' => '/stock']) ?>
