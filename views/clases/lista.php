<?php
/**
 * Listado y búsqueda de clases.
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $clases
 * @var string|null $termino
 */
?>
<?= $this->renderParcial('partials/cabecera', [
  'titulo' => $titulo,
  'buscar' => 'clases',
  'placeholder' => 'Buscar Clase',
  'termino' => $termino ?? '',
]) ?>

<div class="acciones">
  <a class="boton" href="<?= url('/clases/nueva') ?>"><i class="fas fa-chalkboard-teacher"></i> Agregar Clase</a>
</div>

<div class="tabla">
  <?php if (empty($clases)): ?>
    <p>No se encontraron clases.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>Nombre de la Clase</th>
          <th>Precio</th>
          <th>Profesores</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($clases as $clase): ?>
          <tr>
            <td><?= e($clase['name_class']) ?></td>
            <td><?= e(dinero($clase['price_class'])) ?></td>
            <td class="diminuto"><?= e($clase['profesores'] ?: '—') ?></td>
            <td class="celda-acciones">
              <a class="boton button_small" href="<?= url('/clases/' . $clase['id_class']) ?>"><i class="fas fa-eye"></i> Ver +</a>
              <?= $this->renderParcial('clases/_form_eliminar', ['clase' => $clase, 'chico' => true]) ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/navegacion') ?>
