<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Manejo de sesión y mensajes flash (reemplaza los alert() que imprimían los controladores).
 */
final class Session
{
  private const CLAVE_FLASH = '_flash';

  public static function iniciar(): void
  {
    if (session_status() === PHP_SESSION_ACTIVE) {
      return;
    }

    session_name('pfc_session');
    session_set_cookie_params([
      'lifetime' => 0,
      'path' => '/',
      'httponly' => true,
      'samesite' => 'Lax',
      'secure' => (($_SERVER['HTTPS'] ?? 'off') !== 'off'),
    ]);
    session_start();
  }

  public static function get(string $clave, mixed $default = null): mixed
  {
    return $_SESSION[$clave] ?? $default;
  }

  public static function set(string $clave, mixed $valor): void
  {
    $_SESSION[$clave] = $valor;
  }

  public static function regenerar(): void
  {
    session_regenerate_id(true);
  }

  public static function destruir(): void
  {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
      $p = session_get_cookie_params();
      setcookie(session_name(), '', time() - 42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
  }

  /** Tipos: exito | error | aviso */
  public static function flash(string $tipo, string $mensaje): void
  {
    $_SESSION[self::CLAVE_FLASH][] = ['tipo' => $tipo, 'mensaje' => $mensaje];
  }

  /** @return array<int, array{tipo: string, mensaje: string}> */
  public static function tomarFlashes(): array
  {
    $mensajes = $_SESSION[self::CLAVE_FLASH] ?? [];
    unset($_SESSION[self::CLAVE_FLASH]);
    return $mensajes;
  }

  /** Guarda los datos del formulario para re-poblarlo después de un error de validación. */
  public static function guardarViejos(array $datos): void
  {
    $_SESSION['_old'] = $datos;
  }

  public static function tomarViejos(): array
  {
    $datos = $_SESSION['_old'] ?? [];
    unset($_SESSION['_old']);
    return $datos;
  }
}
