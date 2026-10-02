<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 */
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<form action="<?= url('/stock') ?>" method="post" class="form-container">
  <?= csrf_field() ?>
  <?= $this->renderParcial('stock/_campos_producto', ['producto' => [
    'nombre' => old('nombre'),
    'descripcion' => old('descripcion'),
    'precio' => old('precio'),
    'stock_minimo' => old('stock_minimo', '0'),
  ]]) ?>
  <div class="form-group">
    <label for="stock_inicial">Stock inicial:</label>
    <input type="number" id="stock_inicial" name="stock_inicial" min="0" step="1" value="<?= e(old('stock_inicial', '0')) ?>" required>
  </div>
  <div class="form-botones">
    <button type="submit"><i class="fas fa-plus"></i> Crear producto</button>
  </div>
</form>

<?= $this->renderParcial('partials/navegacion', ['volver' => '/stock']) ?>
