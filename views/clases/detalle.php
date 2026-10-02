<?php
/**
 * @var App\Core\View $this
 * @var array $clase
 * @var array $profesores
 * @var array $alumnos
 * @var array $horarios
 * @var int $asistencias Ingresos a esta clase en los últimos 30 días
 */
use App\Services\ConfiguracionService;
$rutaClase = '/clases/' . $clase['id_class'];
$clase['matriculaciones'] = count($profesores) + count($alumnos);
?>
<header class="cabecera">
  <img src="<?= asset('img/logo.png') ?>" alt="Palillo Fight Club" width="100" height="100">
  <h1>Clase: <?= e($clase['name_class']) ?></h1>
  <div class="cabecera-acciones">
    <a class="boton" href="<?= url('/clases') ?>"><i class="fas fa-undo-alt"></i> Volver</a>
    <?= $this->renderParcial('clases/_form_eliminar', ['clase' => $clase]) ?>
  </div>
</header>

<section class="detalle">
  <form action="<?= url($rutaClase . '/actualizar') ?>" method="post" class="form-container">
    <?= csrf_field() ?>
    <div class="form-group">
      <label for="name_class">Nombre de la clase:</label>
      <input type="text" id="name_class" name="name_class" value="<?= e($clase['name_class']) ?>" required>
    </div>
    <div class="form-group">
      <label for="precio">Precio de la clase:</label>
      <input type="number" id="precio" name="precio" min="0" step="1" value="<?= e($clase['price_class']) ?>" required>
    </div>
    <div class="form-botones">
      <button type="submit"><i class="fas fa-sync-alt"></i> Actualizar Clase</button>
    </div>
  </form>

  <h2>Horarios <span class="diminuto">· <?= (int) $asistencias ?> ingreso(s) en los últimos 30 días</span></h2>
  <?php if (empty($horarios)): ?>
    <p class="diminuto">Sin horarios. Cargalos para que cada fichada se asigne a esta clase automáticamente.</p>
  <?php else: ?>
    <ul class="lista-horarios">
      <?php foreach ($horarios as $horario): ?>
        <li>
          <strong><?= e(ConfiguracionService::DIAS_SEMANA[(int) $horario['dia_semana']]) ?></strong>
          <?= e(substr($horario['hora_inicio'], 0, 5)) ?> a <?= e(substr($horario['hora_fin'], 0, 5)) ?>
          <form action="<?= url($rutaClase . '/horarios/' . $horario['id'] . '/quitar') ?>" method="post" data-confirmar="¿Quitar este horario?">
            <?= csrf_field() ?>
            <button type="submit" class="button_small peligro" title="Quitar"><i class="fas fa-times"></i></button>
          </form>
        </li>
      <?php endforeach; ?>
    </ul>
  <?php endif; ?>
  <form action="<?= url($rutaClase . '/horarios') ?>" method="post" class="form-en-linea">
    <?= csrf_field() ?>
    <select name="dia" required aria-label="Día">
      <option value="">Día…</option>
      <?php foreach (ConfiguracionService::DIAS_SEMANA as $numero => $nombre): ?>
        <option value="<?= $numero ?>"><?= e($nombre) ?></option>
      <?php endforeach; ?>
    </select>
    <label>de <input type="time" name="inicio" required></label>
    <label>a <input type="time" name="fin" required></label>
    <button type="submit"><i class="fas fa-clock"></i> Agregar horario</button>
  </form>

  <hr>
  <form action="<?= url($rutaClase . '/miembros') ?>" method="post" class="form-en-linea">
    <?= csrf_field() ?>
    <label for="dni_miembro">Agregar Alumno/ Profesor por DNI:</label>
    <input type="text" id="dni_miembro" name="dni" inputmode="numeric" pattern="\d{7,8}" title="7 u 8 dígitos" required>
    <button type="submit"><i class="fas fa-user-plus"></i> Agregar</button>
  </form>

  <hr>
  <div class="tabla-container">
    <?php foreach ([['Profesores', 'Profesor', $profesores], ['Alumnos', 'Alumno', $alumnos]] as [$grupo, $singular, $miembros]): ?>
      <div class="tabla">
        <h2><?= e($grupo) ?></h2>
        <?php if (empty($miembros)): ?>
          <p>Sin <?= e(mb_strtolower($grupo)) ?> asignados.</p>
        <?php else: ?>
          <table>
            <thead>
              <tr>
                <th><?= e($singular) ?></th>
                <th>Acción</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($miembros as $miembro): ?>
                <tr>
                  <td><a href="<?= url('/usuarios/' . $miembro['dni']) ?>"><?= e(trim($miembro['user_name'] . ' ' . $miembro['user_surname'])) ?></a></td>
                  <td>
                    <form action="<?= url($rutaClase . '/miembros/' . $miembro['id_user'] . '/quitar') ?>" method="post"
                      data-confirmar="¿Quitar a <?= e($miembro['user_name']) ?> de la clase?">
                      <?= csrf_field() ?>
                      <button type="submit" class="button_small peligro"><i class="fas fa-trash"></i> Quitar</button>
                    </form>
                  </td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  </div>
</section>

<?= $this->renderParcial('partials/navegacion', ['volver' => '/clases']) ?>
