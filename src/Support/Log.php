<?php

declare(strict_types=1);

namespace App\Support;

use Throwable;

/**
 * Registro de errores en storage/logs (antes los errores se descartaban en silencio).
 */
final class Log
{
  public static function error(string $mensaje, ?Throwable $e = null): void
  {
    $linea = sprintf('[%s] ERROR %s', date('Y-m-d H:i:s'), $mensaje);
    if ($e !== null) {
      $linea .= sprintf(' | %s: %s en %s:%d', $e::class, $e->getMessage(), $e->getFile(), $e->getLine());
    }
    self::escribir($linea);
  }

  public static function info(string $mensaje): void
  {
    self::escribir(sprintf('[%s] INFO %s', date('Y-m-d H:i:s'), $mensaje));
  }

  private static function escribir(string $linea): void
  {
    $directorio = base_path('storage/logs');
    if (!is_dir($directorio)) {
      @mkdir($directorio, 0775, true);
    }
    $ok = @file_put_contents($directorio . '/app-' . date('Y-m') . '.log', $linea . PHP_EOL, FILE_APPEND | LOCK_EX);
    if ($ok === false) {
      error_log($linea);
    }
  }
}
