<?php
/**
 * Ficha de un cliente o profesor.
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $usuario
 * @var App\Domain\TipoUsuario $tipo
 * @var array $clases
 * @var array $clasesDisponibles
 * @var array $pagos
 */
use App\Domain\TipoUsuario;

$activo = (bool) $usuario['asset'];
$etiqueta = $tipo->etiqueta();
$esAlumno = $tipo === TipoUsuario::Alumno;
$rutaUsuario = '/usuarios/' . $usuario['dni'];
$rutaLista = $tipo === TipoUsuario::Profesor ? '/profesores' : '/clientes';
?>
<header class="cabecera">
  <img src="<?= asset('img/logo.png') ?>" alt="Palillo Fight Club" width="100" height="100">
  <h1><?= e($titulo) ?></h1>
  <div class="cabecera-acciones">
    <a class="boton" href="<?= url($rutaLista) ?>"><i class="fas fa-undo-alt"></i> Volver</a>
    <?php if ($activo): ?>
      <form action="<?= url($rutaUsuario . '/desactivar') ?>" method="post" data-confirmar="¿Inhabilitar a <?= e($usuario['user_name']) ?>?">
        <?= csrf_field() ?>
        <button type="submit" class="peligro"><i class="fas fa-trash"></i> Inhabilitar <?= e($etiqueta) ?></button>
      </form>
    <?php else: ?>
      <form action="<?= url($rutaUsuario . '/reactivar') ?>" method="post">
        <?= csrf_field() ?>
        <button type="submit"><i class="fas fa-user-plus"></i> Re-activar <?= e($etiqueta) ?></button>
      </form>
    <?php endif; ?>
  </div>
</header>

<section class="detalle">
  <?php if (!$activo): ?>
    <p class="estado-inactivo"><?= e($etiqueta) ?> inactivo</p>
  <?php endif; ?>

  <form action="<?= url('/usuarios/' . $usuario['id_user'] . '/actualizar') ?>" method="post" class="form-container">
    <?= csrf_field() ?>
    <input type="hidden" name="dni_original" value="<?= e($usuario['dni']) ?>">

    <div class="form-group">
      <label for="rfid">N° de llavero:</label>
      <input type="text" id="rfid" name="rfid" value="<?= e($usuario['rfid']) ?>">

      <label for="dni">N° de DNI:</label>
      <input type="text" id="dni" name="dni" inputmode="numeric" pattern="\d{7,8}" value="<?= e($usuario['dni']) ?>" required>

      <label for="name">Nombre:</label>
      <input type="text" id="name" name="name" value="<?= e($usuario['user_name']) ?>" required>
    </div>

    <div class="form-group">
      <label for="surname">Apellido:</label>
      <input type="text" id="surname" name="surname" value="<?= e($usuario['user_surname']) ?>">

      <label for="birth_day">Fecha de Nacimiento:</label>
      <input type="date" id="birth_day" name="birth_day" value="<?= e($usuario['birth_day']) ?>">
    </div>

    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?= e($usuario['email']) ?>">

      <label for="phone">Teléfono:</label>
      <input type="tel" id="phone" name="phone" value="<?= e($usuario['phone_number']) ?>">
    </div>

    <?php if ($activo): ?>
      <div class="form-botones">
        <?php if ($tipo !== TipoUsuario::Administrador): ?>
          <label class="texto-peligro">
            <input type="checkbox" name="cambiar_tipo" value="1">
            Convertir en <?= $tipo === TipoUsuario::Profesor ? 'Cliente' : 'Profesor' ?>
          </label>
        <?php endif; ?>
        <button type="submit"><i class="fas fa-sync-alt"></i> Actualizar <?= e($etiqueta) ?></button>
        <?php if ($tipo === TipoUsuario::Profesor): ?>
          <button type="button" disabled title="Módulo de liquidación de profesores pendiente">
            <i class="fas fa-chalkboard-teacher"></i> Liquidar
          </button>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </form>

  <?php if ($esAlumno): ?>
    <hr>
    <h2>Pagos</h2>
    <?php if ($activo): ?>
      <div class="acciones-pago">
        <form action="<?= url($rutaUsuario . '/pagos') ?>" method="post"
          data-confirmar="¿Registrar un pago con fecha de hoy?">
          <?= csrf_field() ?>
          <input type="hidden" name="volver" value="<?= e($rutaUsuario) ?>">
          <button type="submit"><i class="fas fa-wallet"></i> Renovar pago (hoy)</button>
        </form>

        <form action="<?= url('/pagos/manual') ?>" method="post" class="form-en-linea">
          <?= csrf_field() ?>
          <input type="hidden" name="dni" value="<?= e($usuario['dni']) ?>">
          <input type="hidden" name="volver" value="<?= e($rutaUsuario) ?>">
          <label for="fecha_pago">Pago con fecha:</label>
          <input type="date" id="fecha_pago" name="fecha" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
          <button type="submit"><i class="fas fa-hand-paper"></i> Pago manual</button>
        </form>
      </div>
    <?php endif; ?>

    <?php if (!empty($pagos)): ?>
      <h3>Historial de Pagos</h3>
      <table>
        <thead>
          <tr>
            <th>Fecha de Pago</th>
            <th>Fecha de Renovación</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($pagos as $pago): ?>
            <tr>
              <td><?= e(fecha($pago['discharge_date'])) ?></td>
              <td><?= e(fecha($pago['date_of_renovation'])) ?></td>
              <td>
                <form action="<?= url('/pagos/' . $pago['id_payment'] . '/eliminar') ?>" method="post"
                  data-confirmar="¿Eliminar el pago del <?= e(fecha($pago['discharge_date'])) ?>?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="volver" value="<?= e($rutaUsuario) ?>">
                  <button type="submit" class="button_small peligro"><i class="fas fa-trash"></i> Eliminar pago</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No tiene pagos registrados.</p>
    <?php endif; ?>
  <?php endif; ?>

  <?php if ($tipo !== TipoUsuario::Administrador): ?>
    <hr>
    <h2>Clases</h2>

    <?php if ($activo && !empty($clasesDisponibles)): ?>
      <form action="<?= url('/usuarios/' . $usuario['dni'] . '/matricular') ?>" method="post" class="form-en-linea">
        <?= csrf_field() ?>
        <label for="clase_matricular">Matricular en:</label>
        <select id="clase_matricular" name="id_clase" required>
          <option value="">Elegí una clase…</option>
          <?php foreach ($clasesDisponibles as $clase): ?>
            <option value="<?= e($clase['id_class']) ?>"><?= e($clase['name_class']) ?></option>
          <?php endforeach; ?>
        </select>
        <button type="submit"><i class="fas fa-user-plus"></i> Matricular en clase</button>
      </form>
    <?php endif; ?>

    <?php if (empty($clases)): ?>
      <p>No está registrado en ninguna clase.</p>
    <?php else: ?>
      <table>
        <thead>
          <tr>
            <th>Clase</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($clases as $clase): ?>
            <tr>
              <td><?= e($clase['name_class']) ?></td>
              <td class="celda-acciones">
                <a class="boton button_small" href="<?= url('/clases/' . $clase['id_class']) ?>"><i class="fas fa-eye"></i> Ver +</a>
                <form action="<?= url('/clases/' . $clase['id_class'] . '/miembros/' . $usuario['id_user'] . '/quitar') ?>" method="post"
                  data-confirmar="¿Quitar de la clase <?= e($clase['name_class']) ?>?">
                  <?= csrf_field() ?>
                  <input type="hidden" name="dni" value="<?= e($usuario['dni']) ?>">
                  <input type="hidden" name="volver_a" value="usuario">
                  <button type="submit" class="button_small peligro"><i class="fas fa-trash"></i> Quitar de la clase</button>
                </form>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  <?php endif; ?>
</section>

<?= $this->renderParcial('partials/navegacion', ['volver' => $rutaLista]) ?>
