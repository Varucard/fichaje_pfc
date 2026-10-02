<?php

declare(strict_types=1);

namespace App\Core;

final class Csrf
{
  private const CLAVE = '_csrf_token';

  public static function token(): string
  {
    if (empty($_SESSION[self::CLAVE])) {
      $_SESSION[self::CLAVE] = bin2hex(random_bytes(32));
    }
    return $_SESSION[self::CLAVE];
  }

  public static function valido(?string $token): bool
  {
    return is_string($token) && isset($_SESSION[self::CLAVE]) && hash_equals($_SESSION[self::CLAVE], $token);
  }
}
