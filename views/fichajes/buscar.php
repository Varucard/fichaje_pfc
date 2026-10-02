<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var string $termino
 * @var array $fichajes
 */
?>
<?= $this->renderParcial('partials/cabecera', [
  'titulo' => $titulo,
  'buscar' => 'fichajes',
  'placeholder' => 'Buscar Ingreso',
  'termino' => $termino,
]) ?>

<div class="acciones">
  <button type="button" data-accion="fichaje-manual"><i class="fas fa-clock"></i> Registrar Fichada</button>
</div>

<div class="tabla">
  <?php if (empty($fichajes)): ?>
    <p>No se encontraron resultados.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <th>N° de Llavero</th>
          <th>N° de DNI</th>
          <th>Alumno</th>
          <th>Ingreso</th>
          <th>Clase</th>
          <th>Vencimiento cuota</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($fichajes as $fichaje): ?>
          <tr>
            <td><?= e($fichaje['rfid']) ?></td>
            <td><a href="<?= url('/usuarios/' . $fichaje['dni']) ?>"><?= e($fichaje['dni']) ?></a></td>
            <td class="diminuto"><?= e($fichaje['alumno']) ?></td>
            <td class="diminuto"><?= e(fecha_hora($fichaje['addmission_date'])) ?></td>
            <td class="diminuto"><?= $fichaje['clase'] ? e($fichaje['clase']) : '<span class="texto-aviso">Sin clase</span>' ?></td>
            <td class="<?= $fichaje['pago_cerca'] ? 'cerca-de-vencer' : '' ?>"><?= e(fecha($fichaje['date_of_renovation'])) ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/navegacion', ['volver' => '/fichajes']) ?>
<?= $this->renderParcial('partials/dialogos') ?>
