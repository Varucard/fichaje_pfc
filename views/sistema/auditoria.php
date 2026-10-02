<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $filas
 * @var int $total
 * @var int $paginas
 * @var int $pagina
 * @var array $filtros
 * @var array<string, string> $acciones
 */
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<form method="get" class="filtros">
  <label>Desde <input type="date" name="desde" value="<?= e($filtros['desde']) ?>"></label>
  <label>Hasta <input type="date" name="hasta" value="<?= e($filtros['hasta']) ?>"></label>
  <label>Tipo
    <select name="accion">
      <option value="">Todas</option>
      <?php foreach ($acciones as $clave => $etiqueta): ?>
        <option value="<?= e($clave) ?>" <?= $filtros['accion'] === $clave ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Texto / DNI <input type="search" name="texto" value="<?= e($filtros['texto']) ?>"></label>
  <button type="submit"><i class="fas fa-filter"></i> Filtrar</button>
  <a class="boton" href="<?= url('/auditoria') ?>">Limpiar</a>
</form>

<p class="diminuto"><?= (int) $total ?> registro(s)</p>

<div class="tabla">
  <?php if (empty($filas)): ?>
    <p>No hay registros para los filtros elegidos.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Fecha</th>
          <th>Quién</th>
          <th>Acción</th>
          <th>Detalle</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($filas as $fila): ?>
          <tr>
            <td class="diminuto"><?= e(fecha_hora($fila['fecha'])) ?></td>
            <td class="diminuto"><?= e($fila['usuario']) ?></td>
            <td class="diminuto"><code><?= e($fila['accion']) ?></code></td>
            <td class="texto-izquierda">
              <?= e($fila['descripcion']) ?>
              <?php if ($fila['entidad'] === 'usuario' && ctype_digit((string) $fila['entidad_id'])): ?>
                · <a href="<?= e(url('/usuarios/' . $fila['entidad_id'])) ?>">ver</a>
              <?php endif; ?>
              <?php if (!empty($fila['datos'])): ?>
                <details><summary class="diminuto">Datos</summary><pre><?= e(json_encode(json_decode($fila['datos']), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre></details>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/paginacion', ['pagina' => $pagina, 'paginas' => $paginas, 'ruta' => '/auditoria', 'query' => $filtros]) ?>
<?= $this->renderParcial('partials/navegacion') ?>
