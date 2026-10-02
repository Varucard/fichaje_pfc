<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $profesores Profesores activos
 * @var array $alumnos Alumnos activos
 */
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<form action="<?= url('/clases') ?>" method="post" class="form-container">
  <?= csrf_field() ?>

  <div class="form-group">
    <label for="nombre_clase">Nombre de la clase:</label>
    <input type="text" id="nombre_clase" name="nombre_clase" value="<?= e(old('nombre_clase')) ?>" required>
  </div>

  <div class="form-group">
    <label for="precio">Precio:</label>
    <input type="number" id="precio" name="precio" min="0" step="1" placeholder="$" value="<?= e(old('precio')) ?>" required>
  </div>

  <div class="form-group">
    <label for="profesores">Profesor/es:</label>
    <?php if (empty($profesores)): ?>
      <p class="diminuto">No hay profesores activos cargados. Podés asignarlos después desde la clase.</p>
    <?php else: ?>
      <select id="profesores" name="profesores[]" size="<?= min(count($profesores), 5) ?>" multiple>
        <?php foreach ($profesores as $profesor): ?>
          <option value="<?= e($profesor['id_user']) ?>"><?= e(trim($profesor['user_name'] . ' ' . $profesor['user_surname'])) ?></option>
        <?php endforeach; ?>
      </select>
      <small class="diminuto">Ctrl + clic para elegir más de uno.</small>
    <?php endif; ?>
  </div>

  <div class="form-group form-group-ancho">
    <label for="alumnos">Alumnos (opcional):</label>
    <?php if (empty($alumnos)): ?>
      <p class="diminuto">No hay alumnos activos.</p>
    <?php else: ?>
      <input type="search" placeholder="Filtrar por nombre o DNI…" data-filtrar-select="alumnos" aria-label="Filtrar alumnos">
      <select id="alumnos" name="alumnos[]" size="<?= min(count($alumnos), 8) ?>" multiple>
        <?php foreach ($alumnos as $alumno): ?>
          <option value="<?= e($alumno['id_user']) ?>"><?= e(trim($alumno['user_name'] . ' ' . $alumno['user_surname'])) ?> — <?= e($alumno['dni']) ?></option>
        <?php endforeach; ?>
      </select>
      <small class="diminuto">Ctrl + clic para elegir varios.</small>
    <?php endif; ?>
  </div>

  <div class="form-botones">
    <button type="submit"><i class="fas fa-chalkboard-teacher"></i> Registrar Clase</button>
  </div>
</form>

<?= $this->renderParcial('partials/navegacion', ['volver' => '/clases']) ?>
