<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $administradores
 * @var int $yo ID del administrador con la sesión iniciada
 */
use App\Services\AdministradorService;
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<div class="tabla">
  <table>
    <thead><tr><th>DNI</th><th>Nombre</th><th>Estado</th><th>Nueva contraseña</th><th></th></tr></thead>
    <tbody>
      <?php foreach ($administradores as $admin): $id = (int) $admin['id_user']; ?>
        <tr class="<?= $admin['asset'] ? '' : 'inactivo' ?>">
          <td><?= e($admin['dni']) ?></td>
          <td class="texto-izquierda"><?= e(trim($admin['user_name'] . ' ' . $admin['user_surname'])) ?><?= $id === $yo ? ' <span class="diminuto">(vos)</span>' : '' ?></td>
          <td class="diminuto"><?= $admin['asset'] ? ($admin['password'] ? 'Activo' : 'Sin contraseña') : 'Inactivo' ?></td>
          <td>
            <form action="<?= url('/administradores/' . $id . '/password') ?>" method="post" class="form-porcentaje">
              <?= csrf_field() ?>
              <input type="password" name="password" minlength="<?= AdministradorService::LARGO_MINIMO ?>" placeholder="Nueva" autocomplete="new-password" required aria-label="Nueva contraseña">
              <input type="password" name="password2" minlength="<?= AdministradorService::LARGO_MINIMO ?>" placeholder="Repetir" autocomplete="new-password" required aria-label="Repetir contraseña">
              <button type="submit" class="button_small" title="Cambiar contraseña"><i class="fas fa-key"></i></button>
            </form>
          </td>
          <td>
            <?php if ($id !== $yo): ?>
              <form action="<?= url('/administradores/' . $id . '/activo') ?>" method="post" data-confirmar="¿<?= $admin['asset'] ? 'Desactivar' : 'Reactivar' ?> a <?= e($admin['user_name']) ?>?">
                <?= csrf_field() ?>
                <input type="hidden" name="activo" value="<?= $admin['asset'] ? '0' : '1' ?>">
                <button type="submit" class="button_small <?= $admin['asset'] ? 'peligro' : '' ?>"><?= $admin['asset'] ? 'Desactivar' : 'Reactivar' ?></button>
              </form>
            <?php endif; ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<h2>Nuevo administrador</h2>
<form action="<?= url('/administradores') ?>" method="post" class="form-container">
  <?= csrf_field() ?>
  <div class="form-group">
    <label for="dni">DNI (usuario de ingreso):</label>
    <input type="text" id="dni" name="dni" inputmode="numeric" pattern="\d{7,8}" value="<?= e(old('dni')) ?>" required>
    <label for="nombre">Nombre:</label>
    <input type="text" id="nombre" name="nombre" value="<?= e(old('nombre')) ?>" required>
  </div>
  <div class="form-group">
    <label for="apellido">Apellido:</label>
    <input type="text" id="apellido" name="apellido" value="<?= e(old('apellido')) ?>">
    <label for="email">Email:</label>
    <input type="email" id="email" name="email" value="<?= e(old('email')) ?>">
  </div>
  <div class="form-group">
    <label for="password">Contraseña (mínimo <?= AdministradorService::LARGO_MINIMO ?>):</label>
    <input type="password" id="password" name="password" minlength="<?= AdministradorService::LARGO_MINIMO ?>" autocomplete="new-password" required>
    <label for="password2">Repetir contraseña:</label>
    <input type="password" id="password2" name="password2" minlength="<?= AdministradorService::LARGO_MINIMO ?>" autocomplete="new-password" required>
  </div>
  <div class="form-botones">
    <button type="submit"><i class="fas fa-user-shield"></i> Crear administrador</button>
  </div>
</form>

<?= $this->renderParcial('partials/navegacion') ?>
