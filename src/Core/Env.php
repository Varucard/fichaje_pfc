<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Carga variables desde un archivo .env sin dependencias externas.
 * Las variables reales del entorno (por ejemplo, las que define Docker) tienen prioridad.
 */
final class Env
{
  public static function cargar(string $archivo): void
  {
    if (!is_file($archivo)) {
      return;
    }

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];
    foreach ($lineas as $linea) {
      $linea = trim($linea);
      if ($linea === '' || str_starts_with($linea, '#') || !str_contains($linea, '=')) {
        continue;
      }

      [$nombre, $valor] = array_map('trim', explode('=', $linea, 2));
      $valor = trim($valor, "\"'");

      if (getenv($nombre) === false) {
        putenv("{$nombre}={$valor}");
        $_ENV[$nombre] = $valor;
      }
    }
  }

  public static function get(string $clave, ?string $default = null): ?string
  {
    $valor = getenv($clave);
    return ($valor === false || $valor === '') ? $default : $valor;
  }

  public static function bool(string $clave, bool $default = false): bool
  {
    $valor = self::get($clave);
    return $valor === null ? $default : filter_var($valor, FILTER_VALIDATE_BOOLEAN);
  }
}
