<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $entradas
 * @var string $nivel
 * @var string $texto
 * @var array<int, string> $niveles
 */
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<form method="get" class="filtros">
  <label>Nivel mínimo
    <select name="nivel">
      <?php foreach ($niveles as $opcion): ?>
        <option value="<?= e($opcion) ?>" <?= $nivel === $opcion ? 'selected' : '' ?>><?= e($opcion) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Texto <input type="search" name="texto" value="<?= e($texto) ?>"></label>
  <button type="submit"><i class="fas fa-filter"></i> Filtrar</button>
</form>

<p class="diminuto">Últimas <?= count($entradas) ?> entradas de storage/logs (las más nuevas primero).</p>

<div class="tabla">
  <?php if (empty($entradas)): ?>
    <p>No hay entradas para mostrar.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Nivel</th>
          <th>Mensaje</th>
          <th>Petición</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($entradas as $entrada): ?>
          <tr class="log-<?= e($entrada['nivel'] ?? '') ?>">
            <td class="diminuto"><?= e($entrada['fecha'] ?? '') ?></td>
            <td class="diminuto"><span class="etiqueta etiqueta-<?= e($entrada['nivel'] ?? '') ?>"><?= e($entrada['nivel'] ?? '') ?></span></td>
            <td class="texto-izquierda">
              <?= e($entrada['mensaje'] ?? '') ?>
              <?php if (!empty($entrada['datos'])): ?>
                <details><summary class="diminuto">Detalle</summary><pre><?= e(json_encode($entrada['datos'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)) ?></pre></details>
              <?php endif; ?>
            </td>
            <td class="diminuto"><?= e($entrada['peticion'] ?? '') ?><br><?= e($entrada['ip'] ?? '') ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/navegacion') ?>
