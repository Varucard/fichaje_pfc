<?php
/**
 * Baja / alta de avisos por email (página pública, link del email).
 * @var array $usuario
 * @var string $token
 * @var bool|null $confirmado
 */
$acepta = (bool) $usuario['acepta_emails'];
?>
<div class="pagina-error">
  <img src="<?= asset('img/logo.png') ?>" alt="Palillo Fight Club" width="120" height="120">
  <h1>Avisos por email</h1>
  <?php if (!empty($confirmado)): ?>
    <p><?= $acepta ? '¡Listo! Vas a volver a recibir nuestros avisos.' : 'Listo, ya no vas a recibir avisos por email. Los comprobantes de pago se siguen enviando.' ?></p>
  <?php else: ?>
    <p>Hola <?= e($usuario['user_name']) ?>, <?= $acepta ? '¿querés dejar de recibir los avisos (vencimientos, recordatorios, saludos)?' : 'actualmente no recibís avisos por email.' ?></p>
  <?php endif; ?>
  <form method="post" action="<?= url('/emails/baja/' . $token) ?>">
    <?= csrf_field() ?>
    <input type="hidden" name="acepta" value="<?= $acepta ? '0' : '1' ?>">
    <button type="submit"><?= $acepta ? 'Darme de baja de los avisos' : 'Volver a recibir avisos' ?></button>
  </form>
</div>
