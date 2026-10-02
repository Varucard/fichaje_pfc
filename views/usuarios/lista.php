<?php
/**
 * Listado de usuarios: búsqueda, clientes o profesores.
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $usuarios
 * @var string $textoAgregar
 * @var string|null $termino
 * @var bool|null $ocultarLlavero
 */
use App\Domain\TipoUsuario;

$ocultarLlavero ??= false;
?>
<?= $this->renderParcial('partials/cabecera', [
  'titulo' => $titulo,
  'buscar' => 'usuarios',
  'placeholder' => 'Buscar Cliente/ Profesor',
  'termino' => $termino ?? '',
]) ?>

<div class="acciones">
  <a class="boton" href="<?= url('/usuarios/nuevo') ?>"><i class="fas fa-user-plus"></i> <?= e($textoAgregar) ?></a>
</div>

<div class="tabla">
  <?php if (empty($usuarios)): ?>
    <p>No se encontraron resultados.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr>
          <?php if (!$ocultarLlavero): ?><th>Llavero</th><?php endif; ?>
          <th>DNI</th>
          <th>Nombre</th>
          <th>Apellido</th>
          <th>Tipo</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($usuarios as $usuario): ?>
          <tr class="<?= $usuario['asset'] ? '' : 'inactivo' ?>">
            <?php if (!$ocultarLlavero): ?><td><?= e($usuario['rfid']) ?></td><?php endif; ?>
            <td class="destacado"><?= e($usuario['dni']) ?></td>
            <td><?= e($usuario['user_name']) ?></td>
            <td><?= e($usuario['user_surname']) ?></td>
            <td class="diminuto"><?= e(TipoUsuario::deUsuario($usuario)->etiqueta()) ?></td>
            <td class="celda-acciones">
              <a class="boton button_small" href="<?= url('/usuarios/' . $usuario['dni']) ?>">
                <i class="fas fa-user"></i> Ver +
              </a>
              <?php if ($usuario['asset']): ?>
                <form action="<?= url('/usuarios/' . $usuario['dni'] . '/desactivar') ?>" method="post"
                  data-confirmar="¿Desactivar a <?= e($usuario['user_name']) ?>?">
                  <?= csrf_field() ?>
                  <button type="submit" class="button_small peligro"><i class="fas fa-trash"></i> Desactivar</button>
                </form>
              <?php else: ?>
                <span class="texto-peligro">Inactivo</span>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/navegacion') ?>
