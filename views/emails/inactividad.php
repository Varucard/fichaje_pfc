<?php
/** @var array $usuario @var int $dias @var string|null $ultima */
?>
<p>¡Hola <?= e($usuario['user_name']) ?>!</p>
<p>Hace <strong><?= (int) $dias ?> días</strong> que no te vemos por el gimnasio<?= $ultima ? ' (tu última visita fue el ' . e(fecha($ultima)) . ')' : '' ?>. ¡Te extrañamos! 🥊</p>
<p>La constancia es la clave: volver aunque sea una vez esta semana hace la diferencia. Si pasó algo o querés cambiar de horario o de clase, escribinos y lo vemos juntos.</p>
<p>¡Te esperamos en el próximo entrenamiento!</p>
