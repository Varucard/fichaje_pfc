<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Config;
use Throwable;

/**
 * Logs técnicos en storage/logs/app-AAAA-MM-DD.log, una entrada JSON por línea.
 *
 * - Niveles: debug < info < warning < error. Se registra desde LOG_NIVEL (por defecto info).
 * - Cada entrada incluye el contexto de la petición (usuario, IP, método y ruta).
 * - Los archivos más viejos que LOG_DIAS_RETENCION se borran solos una vez por día.
 *
 * Para el historial de acciones de los administradores ver App\Services\AuditoriaService.
 */
final class Log
{
  public const NIVELES = ['debug' => 0, 'info' => 1, 'warning' => 2, 'error' => 3];

  private static array $contexto = [];

  /** Contexto que se agrega a todas las entradas de esta petición. */
  public static function contexto(array $contexto): void
  {
    self::$contexto = array_merge(self::$contexto, $contexto);
  }

  public static function debug(string $mensaje, array $datos = []): void
  {
    self::escribir('debug', $mensaje, $datos);
  }

  public static function info(string $mensaje, array $datos = []): void
  {
    self::escribir('info', $mensaje, $datos);
  }

  public static function warning(string $mensaje, array $datos = []): void
  {
    self::escribir('warning', $mensaje, $datos);
  }

  public static function error(string $mensaje, ?Throwable $e = null, array $datos = []): void
  {
    if ($e !== null) {
      $datos['excepcion'] = [
        'clase' => $e::class,
        'mensaje' => $e->getMessage(),
        'archivo' => $e->getFile() . ':' . $e->getLine(),
        'traza' => array_slice(explode("\n", $e->getTraceAsString()), 0, 15),
      ];
    }
    self::escribir('error', $mensaje, $datos);
  }

  /**
   * Lee las últimas entradas (las más nuevas primero) para el visor del panel.
   *
   * @return array<int, array<string, mixed>>
   */
  public static function ultimas(int $cantidad = 200, ?string $nivelMinimo = null, string $texto = ''): array
  {
    $archivos = glob(self::directorio() . '/app-*.log') ?: [];
    rsort($archivos, SORT_STRING);
    $minimo = self::NIVELES[$nivelMinimo ?? 'debug'] ?? 0;

    $entradas = [];
    foreach ($archivos as $archivo) {
      $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
      foreach (array_reverse($lineas) as $linea) {
        $entrada = json_decode($linea, true);
        if (!is_array($entrada) || (self::NIVELES[$entrada['nivel'] ?? 'debug'] ?? 0) < $minimo) {
          continue;
        }
        if ($texto !== '' && stripos($linea, $texto) === false) {
          continue;
        }
        $entradas[] = $entrada;
        if (count($entradas) >= $cantidad) {
          return $entradas;
        }
      }
    }
    return $entradas;
  }

  private static function escribir(string $nivel, string $mensaje, array $datos): void
  {
    $minimo = self::NIVELES[strtolower((string) Config::get('log.nivel', 'info'))] ?? 1;
    if (self::NIVELES[$nivel] < $minimo) {
      return;
    }

    $entrada = ['fecha' => date('Y-m-d H:i:s'), 'nivel' => $nivel, 'mensaje' => $mensaje]
      + self::$contexto
      + ($datos ? ['datos' => $datos] : []);
    $linea = json_encode($entrada, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PARTIAL_OUTPUT_ON_ERROR);

    $directorio = self::directorio();
    if (!is_dir($directorio)) {
      @mkdir($directorio, 0775, true);
    }

    $archivo = $directorio . '/app-' . date('Y-m-d') . '.log';
    $nuevo = !is_file($archivo);
    if (@file_put_contents($archivo, $linea . PHP_EOL, FILE_APPEND | LOCK_EX) === false) {
      error_log($linea);
      return;
    }

    if ($nuevo) {
      self::limpiarViejos($directorio);
    }
  }

  private static function limpiarViejos(string $directorio): void
  {
    $dias = (int) Config::get('log.dias_retencion', 90);
    if ($dias <= 0) {
      return;
    }
    $limite = time() - $dias * 86400;
    foreach (glob($directorio . '/app-*.log') ?: [] as $archivo) {
      if (filemtime($archivo) < $limite) {
        @unlink($archivo);
      }
    }
  }

  private static function directorio(): string
  {
    return base_path('storage/logs');
  }
}
