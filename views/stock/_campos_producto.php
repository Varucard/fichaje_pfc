<?php
/**
 * Campos comunes del alta y la edición de producto.
 * @var array $producto Valores actuales
 */
?>
<div class="form-group">
  <label for="nombre">Nombre:</label>
  <input type="text" id="nombre" name="nombre" maxlength="120" value="<?= e($producto['nombre'] ?? '') ?>" required>

  <label for="descripcion">Descripción:</label>
  <input type="text" id="descripcion" name="descripcion" maxlength="255" value="<?= e($producto['descripcion'] ?? '') ?>">
</div>
<div class="form-group">
  <label for="precio">Precio de venta $:</label>
  <input type="number" id="precio" name="precio" min="0" step="0.01" value="<?= e($producto['precio'] ?? '') ?>" required>

  <label for="stock_minimo">Stock mínimo (aviso):</label>
  <input type="number" id="stock_minimo" name="stock_minimo" min="0" step="1" value="<?= e($producto['stock_minimo'] ?? '0') ?>" required>
</div>
