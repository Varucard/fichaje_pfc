<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 */
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<form action="<?= url('/usuarios') ?>" method="post" class="form-container">
  <?= csrf_field() ?>

  <div class="form-group">
    <label for="rfid">N° de llavero:</label>
    <input type="text" id="rfid" name="rfid" value="<?= e(old('rfid')) ?>" placeholder="SIN LLAVERO">

    <label for="dni">N° de DNI:</label>
    <input type="text" id="dni" name="dni" inputmode="numeric" pattern="\d{7,8}" title="7 u 8 dígitos" value="<?= e(old('dni')) ?>" required>

    <label for="name">Nombre:</label>
    <input type="text" id="name" name="name" value="<?= e(old('name')) ?>" required>
  </div>

  <div class="form-group">
    <label for="surname">Apellido:</label>
    <input type="text" id="surname" name="surname" value="<?= e(old('surname')) ?>">

    <label for="birth_day">Fecha de nacimiento:</label>
    <input type="date" id="birth_day" name="birth_day" value="<?= e(old('birth_day')) ?>">
  </div>

  <div class="form-group">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?= e(old('email')) ?>">

    <label for="phone">Teléfono:</label>
    <input type="tel" id="phone" name="phone" value="<?= e(old('phone')) ?>">
  </div>

  <fieldset class="opciones-alta">
    <label><input type="radio" name="opcion_alta" value="" checked> Cliente sin pago</label>
    <label><input type="radio" name="opcion_alta" value="pago"> Cliente con pago</label>
    <label><input type="radio" name="opcion_alta" value="profesor" <?= old('profesor') ? 'checked' : '' ?>> Profesor</label>
  </fieldset>

  <div class="form-botones">
    <button type="submit"><i class="fas fa-user-plus"></i> Registrar Cliente/ Profesor</button>
  </div>
</form>

<?= $this->renderParcial('partials/navegacion') ?>
