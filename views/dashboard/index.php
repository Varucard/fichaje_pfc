<?php
/**
 * @var App\Core\View $this
 * @var array $administrador
 * @var int $stockBajo Productos con stock en o debajo del mínimo
 */
$this->script('llaveros-pendientes');
?>
<header class="cabecera cabecera-dashboard">
  <img src="<?= asset('img/logo.png') ?>" alt="Palillo Fight Club" width="200" height="200">
  <h1>Palillo Fight Club</h1>
</header>
<h2 class="centrado">¡Bienvenido/a <?= e($administrador['nombre'] ?? 'Administrador') ?>!</h2>

<section class="buscadores">
  <label class="search-container">
    <i class="fas fa-search"></i>
    <input type="search" data-buscar="fichajes" placeholder="Buscar Ingreso" aria-label="Buscar ingreso">
  </label>
  <label class="search-container">
    <i class="fas fa-search"></i>
    <input type="search" data-buscar="usuarios" placeholder="Buscar Cliente/ Profesor" aria-label="Buscar cliente o profesor">
  </label>
  <label class="search-container">
    <i class="fas fa-search"></i>
    <input type="search" data-buscar="clases" placeholder="Buscar Clase" aria-label="Buscar clase">
  </label>
</section>

<section class="acciones">
  <button type="button" data-accion="fichaje-manual"><i class="fas fa-clock"></i> Registrar Fichada</button>
  <a class="boton" href="<?= url('/usuarios/nuevo') ?>"><i class="fas fa-user-plus"></i> Agregar Cliente/ Profesor</a>
  <button type="button" data-accion="pago-manual"><i class="fas fa-wallet"></i> Abonar Clase</button>
  <a class="boton" href="<?= url('/clases/nueva') ?>"><i class="fas fa-chalkboard-teacher"></i> Agregar Clase</a>
  <a class="boton" href="<?= url('/clases') ?>"><i class="fas fa-chalkboard"></i> Clases</a>
  <a class="boton" href="<?= url('/clientes') ?>"><i class="fas fa-users"></i> Clientes</a>
  <a class="boton" href="<?= url('/profesores') ?>"><i class="fas fa-user-graduate"></i> Profesores</a>
  <a class="boton" href="<?= url('/fichajes') ?>"><i class="fas fa-clipboard-check"></i> Últimos Ingresos</a>
  <a class="boton" href="<?= url('/deudores') ?>"><i class="fas fa-file-invoice-dollar"></i> Deudores</a>
  <a class="boton" href="<?= url('/liquidaciones') ?>"><i class="fas fa-money-check-alt"></i> Liquidaciones</a>
  <a class="boton" href="<?= url('/stock') ?>"><i class="fas fa-boxes"></i> Stock<?php if ($stockBajo): ?> <span class="etiqueta etiqueta-error"><?= (int) $stockBajo ?></span><?php endif; ?></a>
</section>

<section class="acciones acciones-sistema">
  <a class="boton" href="<?= url('/auditoria') ?>"><i class="fas fa-history"></i> Auditoría</a>
  <a class="boton" href="<?= url('/sistema/logs') ?>"><i class="fas fa-file-alt"></i> Logs</a>
  <form action="<?= url('/sistema/reiniciar-arduino') ?>" method="post" data-confirmar="¿Reiniciar el lector Arduino?">
    <?= csrf_field() ?>
    <button type="submit" class="peligro"><i class="fas fa-sync-alt"></i> Reiniciar Arduino</button>
  </form>
  <form action="<?= url('/sistema/backup') ?>" method="post">
    <?= csrf_field() ?>
    <button type="submit" class="peligro"><i class="fas fa-save"></i> Respaldar BD</button>
  </form>
  <form action="<?= url('/logout') ?>" method="post">
    <?= csrf_field() ?>
    <button type="submit" class="peligro"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</button>
  </form>
</section>

<?= $this->renderParcial('partials/modal_cumpleanos') ?>
