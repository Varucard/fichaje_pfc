<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $promociones
 */
use App\Domain\PlanDePago;
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<p class="diminuto">
  Una promoción cobra <strong>N meses</strong> (con descuento opcional) y da <strong>M meses bonificados</strong> de regalo.
  Ej: "3 + 1 gratis" = 3 pagos, 1 bonificado; "Semestral 10 % off" = 6 pagos, 10 % de descuento.
  Al cobrar, el precio se calcula con la cuota del alumno (suma de sus clases).
</p>

<form action="<?= url('/promociones') ?>" method="post" class="filtros">
  <?= csrf_field() ?>
  <label>Nombre <input type="text" name="nombre" maxlength="80" value="<?= e(old('nombre')) ?>" placeholder="3 + 1 gratis" required></label>
  <label>Meses pagos <input type="number" name="meses_pagos" min="1" max="<?= PlanDePago::MAX_MESES ?>" value="<?= e(old('meses_pagos', '3')) ?>" required></label>
  <label>Meses bonificados <input type="number" name="meses_bonificados" min="0" max="<?= PlanDePago::MAX_MESES ?>" value="<?= e(old('meses_bonificados', '1')) ?>" required></label>
  <label>Descuento % <input type="number" name="descuento" min="0" max="99.99" step="0.01" value="<?= e(old('descuento', '0')) ?>"></label>
  <button type="submit"><i class="fas fa-plus"></i> Crear promoción</button>
</form>

<div class="tabla">
  <?php if (empty($promociones)): ?>
    <p>No hay promociones.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr><th>Promoción</th><th>Cobra</th><th>Bonifica</th><th>Descuento</th><th>Cubre</th><th>Usos</th><th>Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($promociones as $promo): ?>
          <tr class="<?= $promo['activa'] ? '' : 'inactivo' ?>">
            <td class="texto-izquierda"><?= e($promo['nombre']) ?><?= $promo['activa'] ? '' : ' <span class="diminuto">(inactiva)</span>' ?></td>
            <td><?= (int) $promo['meses_pagos'] ?> mes(es)</td>
            <td><?= (int) $promo['meses_bonificados'] ?></td>
            <td><?= $promo['descuento'] > 0 ? e($promo['descuento'] + 0) . ' %' : '-' ?></td>
            <td><?= (int) $promo['meses_pagos'] + (int) $promo['meses_bonificados'] ?> mes(es)</td>
            <td><?= (int) $promo['usos'] ?></td>
            <td>
              <form action="<?= url('/promociones/' . $promo['id'] . '/activa') ?>" method="post">
                <?= csrf_field() ?>
                <input type="hidden" name="activa" value="<?= $promo['activa'] ? '0' : '1' ?>">
                <button type="submit" class="button_small <?= $promo['activa'] ? 'peligro' : '' ?>"><?= $promo['activa'] ? 'Desactivar' : 'Activar' ?></button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/navegacion') ?>
