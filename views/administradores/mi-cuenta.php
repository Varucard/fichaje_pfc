<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $administrador
 */
use App\Services\AdministradorService;
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<p>Sesión iniciada como <strong><?= e($administrador['nombre']) ?></strong> (DNI <?= e($administrador['dni']) ?>).</p>

<h2>Cambiar mi contraseña</h2>
<form action="<?= url('/mi-cuenta/password') ?>" method="post" class="form-container">
  <?= csrf_field() ?>
  <div class="form-group">
    <label for="actual">Contraseña actual:</label>
    <input type="password" id="actual" name="actual" autocomplete="current-password" required>
  </div>
  <div class="form-group">
    <label for="password">Nueva contraseña (mínimo <?= AdministradorService::LARGO_MINIMO ?>):</label>
    <input type="password" id="password" name="password" minlength="<?= AdministradorService::LARGO_MINIMO ?>" autocomplete="new-password" required>
  </div>
  <div class="form-group">
    <label for="password2">Repetir nueva contraseña:</label>
    <input type="password" id="password2" name="password2" minlength="<?= AdministradorService::LARGO_MINIMO ?>" autocomplete="new-password" required>
  </div>
  <div class="form-botones">
    <button type="submit"><i class="fas fa-key"></i> Cambiar contraseña</button>
  </div>
</form>

<?= $this->renderParcial('partials/navegacion') ?>
