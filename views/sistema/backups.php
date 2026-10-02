<?php
/**
 * @var App\Core\View $this
 * @var string $titulo
 * @var array $backups
 * @var bool $automatico
 * @var int $hora
 * @var int $retencion
 */
$tamanio = fn (int $bytes) => $bytes >= 1048576 ? number_format($bytes / 1048576, 1, ',', '.') . ' MB' : number_format($bytes / 1024, 0, ',', '.') . ' KB';
?>
<?= $this->renderParcial('partials/cabecera', ['titulo' => $titulo]) ?>

<div class="resumen">
  <div class="resumen-dato <?= $automatico ? 'exito' : 'peligro' ?>">
    <span>Backup automático</span>
    <strong><?= $automatico ? 'Diario desde las ' . (int) $hora . ' h' : 'Desactivado' ?></strong>
  </div>
  <div class="resumen-dato"><span>Se conservan</span><strong><?= $retencion > 0 ? (int) $retencion . ' días' : 'Todos' ?></strong></div>
  <div class="resumen-dato <?= $backups && substr($backups[0]['fecha'], 0, 10) === date('Y-m-d') ? 'exito' : 'peligro' ?>">
    <span>Último backup</span><strong><?= $backups ? e(fecha_hora($backups[0]['fecha'])) : 'Nunca' ?></strong>
  </div>
</div>

<div class="acciones">
  <form action="<?= url('/sistema/backup') ?>" method="post">
    <?= csrf_field() ?>
    <button type="submit"><i class="fas fa-save"></i> Respaldar ahora</button>
  </form>
</div>

<p class="diminuto">
  Guardá periódicamente una copia fuera de este equipo (pendrive, Google Drive): si se rompe el disco, los backups locales se pierden con él.
  Para restaurar: importar el archivo .sql en phpMyAdmin o con <code>mysql -u usuario -p pfc &lt; archivo.sql</code>.
</p>

<div class="tabla">
  <?php if (empty($backups)): ?>
    <p>Todavía no hay backups.</p>
  <?php else: ?>
    <table>
      <thead><tr><th>Archivo</th><th>Fecha</th><th>Tamaño</th><th></th></tr></thead>
      <tbody>
        <?php foreach ($backups as $backup): ?>
          <tr>
            <td class="texto-izquierda diminuto"><?= e($backup['nombre']) ?></td>
            <td class="diminuto"><?= e(fecha_hora($backup['fecha'])) ?></td>
            <td class="diminuto"><?= e($tamanio($backup['tamanio'])) ?></td>
            <td><a class="boton button_small" href="<?= url('/sistema/backups/' . $backup['nombre']) ?>"><i class="fas fa-download"></i> Descargar</a></td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  <?php endif; ?>
</div>

<?= $this->renderParcial('partials/navegacion') ?>
