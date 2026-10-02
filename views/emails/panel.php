<?php
/**
 * Panel de emails: configuración de avisos, prueba de envío y cola.
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $config
 * @var array $opciones
 * @var array $dias
 * @var array $tipos
 * @var array $filas
 * @var int $total
 * @var int $paginas
 * @var int $pagina
 * @var array $por_estado
 * @var array $filtros
 */
use App\Core\Config;

$modo = Config::get('mail.modo');
$etiquetasEstado = ['pendiente' => 'warning', 'enviado' => 'exito', 'error' => 'error', 'cancelado' => 'neutra'];
$campo = fn (string $clave) => str_replace('.', '_', $clave);
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<div class="resumen">
  <div class="resumen-dato <?= $modo === 'smtp' ? 'exito' : '' ?>">
    <span>Modo de envío</span>
    <strong><?= $modo === 'smtp' ? e(Config::get('mail.host')) : 'Archivo (prueba)' ?></strong>
  </div>
  <div class="resumen-dato"><span>Pendientes</span><strong><?= (int) ($por_estado['pendiente'] ?? 0) ?></strong></div>
  <div class="resumen-dato exito"><span>Enviados</span><strong><?= (int) ($por_estado['enviado'] ?? 0) ?></strong></div>
  <div class="resumen-dato <?= !empty($por_estado['error']) ? 'peligro' : '' ?>"><span>Con error</span><strong><?= (int) ($por_estado['error'] ?? 0) ?></strong></div>
</div>
<?php if ($modo !== 'smtp'): ?>
  <p class="diminuto texto-peligro">MAIL_MODO=log: los emails no se envían, se guardan en storage/emails. Configurá MAIL_* en el .env para enviar de verdad.</p>
<?php endif; ?>

<div class="acciones">
  <form action="<?= url('/emails/procesar') ?>" method="post">
    <?= csrf_field() ?>
    <button type="submit"><i class="fas fa-paper-plane"></i> Generar avisos y enviar ahora</button>
  </form>
  <form action="<?= url('/emails/prueba') ?>" method="post" class="form-en-linea">
    <?= csrf_field() ?>
    <input type="email" name="destinatario" placeholder="tu@email.com" required aria-label="Email de prueba">
    <button type="submit"><i class="fas fa-vial"></i> Enviar prueba</button>
  </form>
</div>

<details class="bloque">
  <summary><strong>Configuración de avisos</strong></summary>
  <form action="<?= url('/emails/configuracion') ?>" method="post" class="config-avisos">
    <?= csrf_field() ?>
    <?php foreach ($opciones as $clave => [, $tipo, $etiqueta]): ?>
      <label class="<?= $tipo === 'bool' ? 'opcion-check' : 'opcion-valor' ?>">
        <?php if ($tipo === 'bool'): ?>
          <input type="checkbox" name="<?= e($campo($clave)) ?>" value="1" <?= $config[$clave] === '1' ? 'checked' : '' ?>>
          <span><?= e($etiqueta) ?></span>
        <?php elseif ($tipo === 'dia_semana'): ?>
          <span><?= e($etiqueta) ?></span>
          <select name="<?= e($campo($clave)) ?>">
            <?php foreach ($dias as $numero => $nombre): ?>
              <option value="<?= $numero ?>" <?= (int) $config[$clave] === $numero ? 'selected' : '' ?>><?= e($nombre) ?></option>
            <?php endforeach; ?>
          </select>
        <?php else: ?>
          <span><?= e($etiqueta) ?></span>
          <input type="<?= $tipo === 'dias' ? 'number' : ($tipo === 'email' ? 'email' : 'text') ?>"
            name="<?= e($campo($clave)) ?>" value="<?= e($config[$clave]) ?>"
            <?= $tipo === 'dias' ? 'min="1" max="365" required' : '' ?>>
        <?php endif; ?>
      </label>
    <?php endforeach; ?>
    <div class="form-botones">
      <button type="submit"><i class="fas fa-save"></i> Guardar configuración</button>
    </div>
  </form>
</details>

<h2>Envíos</h2>
<form method="get" class="filtros">
  <label>Estado
    <select name="estado">
      <option value="">Todos</option>
      <?php foreach (array_keys($etiquetasEstado) as $estado): ?>
        <option value="<?= $estado ?>" <?= $filtros['estado'] === $estado ? 'selected' : '' ?>><?= ucfirst($estado) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <label>Tipo
    <select name="tipo">
      <option value="">Todos</option>
      <?php foreach ($tipos as $clave => $etiqueta): ?>
        <option value="<?= e($clave) ?>" <?= $filtros['tipo'] === $clave ? 'selected' : '' ?>><?= e($etiqueta) ?></option>
      <?php endforeach; ?>
    </select>
  </label>
  <button type="submit"><i class="fas fa-filter"></i> Filtrar</button>
</form>

<div class="tabla">
  <?php if (empty($filas)): ?>
    <p>No hay emails.</p>
  <?php else: ?>
    <table>
      <thead>
        <tr><th>Fecha</th><th>Tipo</th><th>Para</th><th>Asunto</th><th>Estado</th><th>Acciones</th></tr>
      </thead>
      <tbody>
        <?php foreach ($filas as $email): ?>
          <tr>
            <td class="diminuto"><?= e(fecha_hora($email['enviado_en'] ?? $email['creado_en'])) ?></td>
            <td class="diminuto"><?= e($tipos[$email['tipo']] ?? $email['tipo']) ?></td>
            <td class="diminuto texto-izquierda"><?= e($email['nombre_destinatario'] ?: '') ?><br><?= e($email['destinatario']) ?></td>
            <td class="texto-izquierda diminuto"><?= e($email['asunto']) ?><?= $email['adjunto'] ? ' 📎' : '' ?></td>
            <td>
              <span class="etiqueta etiqueta-<?= e($etiquetasEstado[$email['estado']]) ?>"><?= e($email['estado']) ?></span>
              <?php if ($email['ultimo_error']): ?>
                <details><summary class="diminuto">Error (<?= (int) $email['intentos'] ?>)</summary><span class="diminuto"><?= e($email['ultimo_error']) ?></span></details>
              <?php endif; ?>
            </td>
            <td class="celda-acciones">
              <a class="boton button_small" href="<?= url('/emails/' . $email['id']) ?>" target="_blank" rel="noopener"><i class="fas fa-eye"></i> Ver</a>
              <?php if (in_array($email['estado'], ['error', 'cancelado'], true)): ?>
                <form action="<?= url('/emails/' . $email['id'] . '/reintentar') ?>" method="post"><?= csrf_field() ?><button type="submit" class="button_small"><i class="fas fa-redo"></i> Reintentar</button></form>
              <?php elseif ($email['estado'] === 'pendiente'): ?>
                <form action="<?= url('/emails/' . $email['id'] . '/cancelar') ?>" method="post"><?= csrf_field() ?><button type="submit" class="button_small peligro"><i class="fas fa-times"></i> Cancelar</button></form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/paginacion', ['pagina' => $pagina, 'paginas' => $paginas, 'ruta' => '/emails', 'query' => $filtros]) ?>
<?= $this->renderParcial('partials/navegacion') ?>
