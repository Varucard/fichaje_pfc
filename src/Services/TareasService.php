<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Support\Log;
use DateTimeImmutable;
use Throwable;

/**
 * Tareas programadas (bin/tareas.php, cada 15 minutos):
 * - Genera los avisos por email y envía la cola.
 * - Backup diario de la base a partir de BACKUP_HORA, y limpieza de backups viejos.
 * - Controla que el lector Arduino responda y avisa al administrador si no.
 *
 * Cada tarea es independiente: si una falla, las demás se ejecutan igual.
 */
final class TareasService
{
  public function __construct(
    private readonly AvisosService $avisos,
    private readonly EmailService $emails,
    private readonly SistemaService $sistema,
    private readonly ConfiguracionService $config,
  ) {
  }

  /** @return array<string, mixed> Resumen de lo que se hizo, para la salida del script. */
  public function ejecutar(?DateTimeImmutable $ahora = null): array
  {
    $ahora ??= new DateTimeImmutable();
    $resumen = [];

    $resumen['backup'] = $this->intentar('backup', fn () => $this->backupDiario($ahora));
    $resumen['lector'] = $this->intentar('lector', fn () => $this->controlarLector($ahora));
    $resumen['avisos'] = $this->intentar('avisos', fn () => $this->avisos->generar());
    // La cola va última para enviar también las alertas generadas arriba.
    $resumen['emails'] = $this->intentar('emails', fn () => $this->emails->procesarCola(100));

    return $resumen;
  }

  /** Corresponde el backup automático si está activo, ya es la hora y todavía no se hizo hoy. */
  public static function correspondeBackup(bool $automatico, int $hora, bool $hayBackupDeHoy, DateTimeImmutable $ahora): bool
  {
    return $automatico && !$hayBackupDeHoy && (int) $ahora->format('G') >= $hora;
  }

  private function backupDiario(DateTimeImmutable $ahora): string
  {
    if (!self::correspondeBackup(
      (bool) Config::get('backups.automatico', true),
      (int) Config::get('backups.hora', 3),
      $this->sistema->hayBackupDeHoy(),
      $ahora
    )) {
      return 'no corresponde';
    }

    try {
      $archivo = $this->sistema->generarBackup();
    } catch (Throwable $e) {
      $this->alertar(
        'backup:' . $ahora->format('Y-m-d'),
        'Falló el backup automático',
        'No se pudo generar el respaldo diario de la base de datos. Revisá el espacio en disco y la conexión a la base, y generá uno a mano desde el panel (Sistema → Backups).',
        $e->getMessage()
      );
      throw $e;
    }

    $borrados = $this->sistema->limpiarBackups((int) Config::get('backups.dias_retencion', 30));
    return basename($archivo) . ($borrados ? " ({$borrados} viejo/s borrado/s)" : '');
  }

  private function controlarLector(DateTimeImmutable $ahora): string
  {
    $estado = $this->sistema->estadoLector();
    if ($estado['configurado'] === false) {
      return 'sin configurar';
    }
    if ($estado['en_linea'] ?? false) {
      return 'en línea';
    }

    // Una alerta como máximo cada 6 horas mientras siga sin responder.
    $bloque = intdiv((int) $ahora->format('G'), 6);
    $this->alertar(
      'lector:' . $ahora->format('Y-m-d') . ':' . $bloque,
      'El lector de la entrada no responde',
      "El lector Arduino ({$estado['ip']}) no responde. Los alumnos no van a poder fichar con el llavero: revisá que esté encendido y con el cable de red conectado, o reinicialo desde el panel.",
      $estado['ultima_lectura'] ? 'Última lectura recibida: ' . fecha_hora($estado['ultima_lectura']) : 'Todavía no se registraron lecturas.'
    );
    return 'SIN RESPUESTA';
  }

  private function alertar(string $clave, string $titulo, string $mensaje, ?string $detalle): void
  {
    Log::warning($titulo, ['detalle' => $detalle]);
    $destinatario = $this->config->get('aviso.resumen.destinatario');
    if ($destinatario !== '') {
      $this->emails->encolarDirecto('alerta', $destinatario, '⚠️ ' . $titulo, [
        'titulo_alerta' => $titulo,
        'mensaje' => $mensaje,
        'detalle' => $detalle,
      ], 'alerta:' . $clave);
    }
  }

  private function intentar(string $tarea, callable $accion): mixed
  {
    try {
      return $accion();
    } catch (Throwable $e) {
      Log::error("Falló la tarea programada: {$tarea}", $e);
      return 'ERROR: ' . $e->getMessage();
    }
  }
}
