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
 * @var array|null $deuda
 * @var array $fichajes
 * @var array $historial
 * @var array $emails
 * @var array<int, App\Domain\PlanDePago> $promociones
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
    <?php if ($tipo === TipoUsuario::Administrador): ?>
      <a class="boton" href="<?= url('/administradores') ?>"><i class="fas fa-user-shield"></i> Gestionar en Administradores</a>
    <?php elseif ($activo): ?>
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
      <input type="text" id="rfid" name="rfid" value="<?= e(old('rfid', $usuario['rfid'])) ?>">

      <label for="dni">N° de DNI:</label>
      <input type="text" id="dni" name="dni" inputmode="numeric" pattern="\d{7,8}" value="<?= e($usuario['dni']) ?>" required>

      <label for="name">Nombre:</label>
      <input type="text" id="name" name="name" value="<?= e(old('name', $usuario['user_name'])) ?>" required>
    </div>

    <div class="form-group">
      <label for="surname">Apellido:</label>
      <input type="text" id="surname" name="surname" value="<?= e(old('surname', $usuario['user_surname'])) ?>">

      <label for="birth_day">Fecha de Nacimiento:</label>
      <input type="date" id="birth_day" name="birth_day" value="<?= e(old('birth_day', $usuario['birth_day'])) ?>">
    </div>

    <div class="form-group">
      <label for="email">Email:</label>
      <input type="email" id="email" name="email" value="<?= e(old('email', $usuario['email'])) ?>">

      <label for="phone">Teléfono:</label>
      <input type="tel" id="phone" name="phone" value="<?= e(old('phone', $usuario['phone_number'])) ?>">
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
          <a class="boton" href="<?= url('/liquidaciones', ['profesor' => $usuario['dni']]) ?>">
            <i class="fas fa-money-check-alt"></i> Liquidar
          </a>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </form>

  <?php if ($esAlumno): ?>
    <hr>
    <h2>Pagos</h2>

    <div class="resumen">
      <div class="resumen-dato"><span>Cuota mensual</span><strong><?= e(dinero($deuda['cuota'])) ?></strong></div>
      <div class="resumen-dato"><span>Vence</span><strong><?= e($deuda['renovacion'] ? fecha($deuda['renovacion']) : 'Sin pagos') ?></strong></div>
      <div class="resumen-dato <?= $deuda['total'] > 0 ? 'peligro' : 'exito' ?>">
        <span><?php
          $partes = [];
          if ($deuda['meses'] > 0) $partes[] = (int) $deuda['meses'] . ' cuota(s) vencida(s)';
          if ($deuda['saldo_pagos'] > 0) $partes[] = 'saldo de pagos parciales ' . e(dinero($deuda['saldo_pagos']));
          echo 'Deuda' . ($partes ? ': ' . implode(' + ', $partes) : '');
        ?></span>
        <strong><?= $deuda['total'] > 0 ? e(dinero($deuda['total'])) : 'Al día' ?></strong>
      </div>
    </div>

    <?php if ($activo): ?>
      <div class="acciones-pago">
        <form action="<?= url($rutaUsuario . '/pagos') ?>" method="post"
          data-confirmar="¿Registrar un pago de <?= e(dinero($deuda['cuota'])) ?> con fecha de hoy?">
          <?= csrf_field() ?>
          <input type="hidden" name="volver" value="<?= e($rutaUsuario) ?>">
          <button type="submit"><i class="fas fa-wallet"></i> Renovar pago hoy (<?= e(dinero($deuda['cuota'])) ?>)</button>
        </form>

        <form action="<?= url('/pagos/manual') ?>" method="post" class="form-en-linea" data-form-pago data-cuota="<?= e((string) $deuda['cuota']) ?>">
          <?= csrf_field() ?>
          <input type="hidden" name="dni" value="<?= e(old('dni', $usuario['dni'])) ?>">
          <input type="hidden" name="volver" value="<?= e($rutaUsuario) ?>">
          <label for="plan_pago">Plan:</label>
          <select id="plan_pago" name="plan">
            <?php foreach ([1, 2, 3, 6, 12] as $meses): ?>
              <option value="meses:<?= $meses ?>" data-pagos="<?= $meses ?>" data-bonificados="0" data-descuento="0"><?= $meses === 1 ? '1 mes' : "{$meses} meses" ?></option>
            <?php endforeach; ?>
            <?php foreach ($promociones as $promo): ?>
              <option value="promo:<?= (int) $promo->idPromocion ?>" data-pagos="<?= $promo->mesesPagos ?>"
                data-bonificados="<?= $promo->mesesBonificados ?>" data-descuento="<?= e((string) $promo->descuento) ?>">🏷️ <?= e($promo->descripcion()) ?></option>
            <?php endforeach; ?>
          </select>
          <label for="fecha_pago">Fecha:</label>
          <input type="date" id="fecha_pago" name="fecha" value="<?= date('Y-m-d') ?>" max="<?= date('Y-m-d') ?>" required>
          <label for="monto_pago">Monto $:</label>
          <input type="number" id="monto_pago" name="monto" min="0" step="0.01" value="<?= e((string) $deuda['cuota']) ?>" class="input-monto" required>
          <button type="submit"><i class="fas fa-hand-paper"></i> Registrar pago</button>
          <span class="diminuto" data-detalle-plan></span>
        </form>
      </div>
      <?php if ($deuda['renovacion'] && $deuda['meses'] === 0): ?>
        <p class="diminuto">💡 La cuota está al día hasta el <?= e(fecha($deuda['renovacion'])) ?>: si paga ahora, los meses se suman desde esa fecha (no pierde días).</p>
      <?php elseif ($deuda['meses'] > 0): ?>
        <p class="diminuto">💡 Adeuda <?= (int) $deuda['meses'] ?> cuota(s): el próximo pago cubre primero la cuota más vieja adeudada.</p>
      <?php endif; ?>
      <?php if ($deuda['cuota'] <= 0): ?>
        <p class="diminuto texto-peligro">El alumno no está en ninguna clase: su cuota es $0.</p>
      <?php endif; ?>
    <?php endif; ?>

    <?php if (!empty($pagos)): ?>
      <h3>Historial de Pagos</h3>
      <table>
        <thead>
          <tr>
            <th>Fecha de Pago</th>
            <th>Vencimiento</th>
            <th>Monto</th>
            <th>Acciones</th>
          </tr>
        </thead>
        <tbody>
          <?php $idUltimoPago = max(array_map('intval', array_column($pagos, 'id_payment'))); ?>
          <?php foreach ($pagos as $pago): ?>
            <tr>
              <td><?= e(fecha($pago['discharge_date'])) ?></td>
              <td>
                <?= e(fecha($pago['date_of_renovation'])) ?>
                <?php if ((int) $pago['meses_cubiertos'] > 1 || $pago['promocion']): ?>
                  <br><span class="diminuto"><?= (int) $pago['meses_cubiertos'] ?> meses<?= $pago['promocion'] ? ' · ' . e($pago['promocion']) : '' ?></span>
                <?php endif; ?>
              </td>
              <td>
                <?php if ($pago['monto'] === null): ?>
                  <span class="diminuto">Sin dato</span>
                <?php else: ?>
                  <?= e(dinero($pago['monto'])) ?>
                  <?php if ($pago['monto_cuota'] !== null && (float) $pago['monto'] < (float) $pago['monto_cuota']): ?>
                    <br><span class="diminuto texto-peligro">Parcial (cuota <?= e(dinero($pago['monto_cuota'])) ?>)</span>
                  <?php endif; ?>
                <?php endif; ?>
              </td>
              <td class="celda-acciones">
                <a class="boton button_small" href="<?= url('/pagos/' . $pago['id_payment'] . '/comprobante') ?>" target="_blank" rel="noopener"><i class="fas fa-file-pdf"></i> Comprobante</a>
                <?php if ((int) $pago['id_payment'] === $idUltimoPago): ?>
                  <form action="<?= url('/pagos/' . $pago['id_payment'] . '/eliminar') ?>" method="post"
                    data-confirmar="¿Eliminar el pago del <?= e(fecha($pago['discharge_date'])) ?>?">
                    <?= csrf_field() ?>
                    <input type="hidden" name="volver" value="<?= e($rutaUsuario) ?>">
                    <button type="submit" class="button_small peligro"><i class="fas fa-trash"></i> Eliminar pago</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php else: ?>
      <p>No tiene pagos registrados.</p>
    <?php endif; ?>

    <hr>
    <h2>Últimos ingresos</h2>
    <?php if (empty($fichajes)): ?>
      <p>Todavía no registró ingresos.</p>
    <?php else: ?>
      <ul class="lista-fichajes">
        <?php foreach ($fichajes as $ingreso): ?>
          <li>
            <i class="fas fa-clipboard-check"></i> <?= e(fecha_hora($ingreso['addmission_date'])) ?>
            <span class="<?= $ingreso['clase'] ? 'diminuto' : 'diminuto texto-aviso' ?>">· <?= e($ingreso['clase'] ?: 'Sin clase') ?></span>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (!empty($liquidaciones)): ?>
    <hr>
    <h2>Liquidaciones</h2>
    <table>
      <thead>
        <tr><th>Período</th><th>Base</th><th>Modo</th><th>Monto</th><th>Estado</th></tr>
      </thead>
      <tbody>
        <?php foreach ($liquidaciones as $liquidacion): ?>
          <tr>
            <td><a href="<?= url('/liquidaciones', ['periodo' => $liquidacion['periodo']]) ?>"><?= e($liquidacion['periodo']) ?></a></td>
            <td><?= $liquidacion['modo'] === 'asistencia' ? e($liquidacion['monto_base'] + 0) . ' asist.' : e(dinero($liquidacion['monto_base'])) ?></td>
            <td><?= $liquidacion['modo'] === 'asistencia' ? e(dinero($liquidacion['monto_por_asistencia'])) . ' / asist.' : e($liquidacion['porcentaje'] + 0) . '%' ?></td>
            <td><?= e(dinero($liquidacion['monto'])) ?></td>
            <td><?= $liquidacion['pagada'] ? 'Pagada ' . e(fecha($liquidacion['fecha_pago'])) : 'Pendiente de pago' ?></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
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

  <?php if ($tipo !== TipoUsuario::Administrador): ?>
    <hr>
    <h2>Emails</h2>
    <?php if (empty($usuario['email'])): ?>
      <p class="diminuto texto-peligro">Sin email cargado: no recibe avisos ni comprobantes. Cargalo en los datos de arriba.</p>
    <?php else: ?>
      <form action="<?= url($rutaUsuario . '/emails') ?>" method="post" class="form-en-linea">
        <?= csrf_field() ?>
        <input type="hidden" name="acepta" value="<?= $usuario['acepta_emails'] ? '0' : '1' ?>">
        <span><?= $usuario['acepta_emails'] ? '✅ Recibe avisos por email' : '⛔ Se dio de baja de los avisos (los comprobantes se envían igual)' ?></span>
        <button type="submit" class="button_small"><?= $usuario['acepta_emails'] ? 'Dar de baja de avisos' : 'Volver a enviar avisos' ?></button>
      </form>
    <?php endif; ?>
    <?php if (!empty($emails)): ?>
      <table>
        <thead><tr><th>Fecha</th><th>Asunto</th><th>Estado</th></tr></thead>
        <tbody>
          <?php foreach ($emails as $email): ?>
            <tr>
              <td class="diminuto"><?= e(fecha_hora($email['enviado_en'] ?? $email['creado_en'])) ?></td>
              <td class="texto-izquierda"><a href="<?= url('/emails/' . $email['id']) ?>" target="_blank" rel="noopener"><?= e($email['asunto']) ?></a></td>
              <td class="diminuto"><?= e($email['estado']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (!empty($historial)): ?>
    <hr>
    <details>
      <summary><strong>Historial de cambios</strong> (<?= count($historial) ?>)</summary>
      <table>
        <thead>
          <tr><th>Fecha</th><th>Quién</th><th>Detalle</th></tr>
        </thead>
        <tbody>
          <?php foreach ($historial as $registro): ?>
            <tr>
              <td class="diminuto"><?= e(fecha_hora($registro['fecha'])) ?></td>
              <td class="diminuto"><?= e($registro['usuario']) ?></td>
              <td class="texto-izquierda"><?= e($registro['descripcion']) ?></td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <a href="<?= url('/auditoria', ['texto' => $usuario['dni']]) ?>">Ver todo en Auditoría</a>
    </details>
  <?php endif; ?>
</section>

<?= $this->renderParcial('partials/navegacion', ['volver' => $rutaLista]) ?>
