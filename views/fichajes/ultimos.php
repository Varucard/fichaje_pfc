<?php
/**
 * Últimos ingresos (se actualiza automáticamente cada 5 segundos).
 * @var App\Core\View $this
 * @var string $titulo
 */
$this->script('fichajes-en-vivo');
?>
<?= $this->renderParcial('partials/cabecera', [
  'titulo' => $titulo,
  'buscar' => 'fichajes',
  'placeholder' => 'Buscar Ingreso',
]) ?>

<div class="acciones">
  <button type="button" data-accion="fichaje-manual"><i class="fas fa-clock"></i> Registrar Fichada</button>
</div>

<p id="mensaje-vacio" class="oculto">No hay ingresos registrados.</p>

<div id="contenedor-tabla" class="tabla">
  <table id="tabla-fichajes">
    <thead>
      <tr>
        <th>N° de Llavero</th>
        <th>N° de DNI</th>
        <th>Alumno</th>
        <th>Ingreso</th>
        <th>Vencimiento cuota</th>
      </tr>
    </thead>
    <tbody>
      <!-- Se completa desde public/js/fichajes-en-vivo.js -->
    </tbody>
  </table>
</div>

<?= $this->renderParcial('partials/navegacion') ?>
<?= $this->renderParcial('partials/modal_cumpleanos') ?>
