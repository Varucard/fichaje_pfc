<?php

declare(strict_types=1);

namespace App\Domain;

final class Llavero
{
  /** Valor usado cuando el usuario no tiene un llavero RFID asignado. */
  public const SIN_LLAVERO = 'SIN LLAVERO';

  /** Normaliza el código leído o tipeado: sin espacios y en mayúsculas (como lo envía el Arduino). */
  public static function normalizar(?string $rfid): string
  {
    $rfid = trim((string) $rfid);
    if ($rfid === '' || strcasecmp($rfid, self::SIN_LLAVERO) === 0) {
      return self::SIN_LLAVERO;
    }
    return strtoupper(preg_replace('/\s+/', '', $rfid));
  }

  public static function asignado(?string $rfid): bool
  {
    return self::normalizar($rfid) !== self::SIN_LLAVERO;
  }
}
