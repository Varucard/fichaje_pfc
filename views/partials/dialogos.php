<?php
/**
 * Ventanas de acciones rápidas (reemplazan a los prompt() del navegador).
 * @var array<int, App\Domain\PlanDePago>|null $promociones Para el plan del pago rápido
 * @var bool|null $conPago Incluir la ventana de pago
 */
?>
<dialog id="dialogo-fichaje-manual" class="dialogo" aria-labelledby="titulo-dialogo-fichaje">
  <form action="<?= url('/fichajes/manual') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="volver" value="/dashboard" data-volver-actual>
    <h3 id="titulo-dialogo-fichaje"><i class="fas fa-clock"></i> Registrar fichada</h3>
    <label for="dni-fichaje">DNI del alumno</label>
    <input type="text" id="dni-fichaje" name="dni" inputmode="numeric" pattern="\d{7,8}" title="7 u 8 dígitos, sin puntos" required autofocus>
    <div class="dialogo-botones">
      <button type="button" data-cerrar-dialogo>Cancelar</button>
      <button type="submit"><i class="fas fa-check"></i> Registrar</button>
    </div>
  </form>
</dialog>

<?php if (!empty($conPago)): ?>
<dialog id="dialogo-pago-manual" class="dialogo" aria-labelledby="titulo-dialogo-pago">
  <form action="<?= url('/pagos/manual') ?>" method="post">
    <?= csrf_field() ?>
    <input type="hidden" name="volver" value="/dashboard" data-volver-actual>
    <h3 id="titulo-dialogo-pago"><i class="fas fa-wallet"></i> Abonar clase</h3>
    <label for="dni-pago">DNI del cliente</label>
    <input type="text" id="dni-pago" name="dni" inputmode="numeric" pattern="\d{7,8}" title="7 u 8 dígitos, sin puntos" required autofocus>
    <label for="plan-pago-rapido">Plan</label>
    <select id="plan-pago-rapido" name="plan">
      <?php foreach ([1, 2, 3, 6, 12] as $meses): ?>
        <option value="meses:<?= $meses ?>"><?= $meses === 1 ? '1 mes' : "{$meses} meses" ?></option>
      <?php endforeach; ?>
      <?php foreach ($promociones ?? [] as $promo): ?>
        <option value="promo:<?= (int) $promo->idPromocion ?>">🏷️ <?= e($promo->descripcion()) ?></option>
      <?php endforeach; ?>
    </select>
    <label for="fecha-pago-rapido">Fecha de pago</label>
    <input type="date" id="fecha-pago-rapido" name="fecha" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" data-hoy required>
    <label for="monto-pago-rapido">Monto $ <span class="diminuto">(vacío = precio del plan según sus clases)</span></label>
    <input type="number" id="monto-pago-rapido" name="monto" min="0" step="0.01" placeholder="Precio del plan">
    <div class="dialogo-botones">
      <button type="button" data-cerrar-dialogo>Cancelar</button>
      <button type="submit"><i class="fas fa-check"></i> Registrar pago</button>
    </div>
  </form>
</dialog>
<?php endif; ?>
