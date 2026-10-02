<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
  /** Huella de las credenciales: si cambia la contraseña, las sesiones abiertas dejan de valer. */
  public static function huella(array $usuario): string
  {
    return substr(hash('sha256', (string) ($usuario['password'] ?? '')), 0, 16);
  }

  public static function iniciarSesion(array $usuario): void
  {
    Session::regenerar();
    Session::set('auth', [
      'id' => (int) $usuario['id_user'],
      'nombre' => $usuario['user_name'],
      'dni' => (string) $usuario['dni'],
      'huella' => self::huella($usuario),
    ]);
    Session::set('ultima_actividad', time());
  }

  /**
   * Cierra la sesión si pasó más tiempo que el permitido sin actividad.
   * $registrarActividad = false para las consultas automáticas (polling) de las pantallas,
   * que no deben mantener viva una sesión abandonada.
   *
   * @return bool true si la sesión sigue vigente.
   */
  public static function mantenerVigente(bool $registrarActividad): bool
  {
    if (!self::check()) {
      return false;
    }
    $limite = (int) Config::get('login.inactividad_minutos', 120) * 60;
    $ultima = (int) Session::get('ultima_actividad', time());
    if ($limite > 0 && time() - $ultima > $limite) {
      self::cerrarSesion();
      Session::iniciar();
      Session::flash('aviso', 'Tu sesión se cerró por inactividad. Volvé a ingresar.');
      return false;
    }
    if ($registrarActividad) {
      Session::set('ultima_actividad', time());
    }
    return true;
  }

  public static function cerrarSesion(): void
  {
    Session::destruir();
  }

  public static function check(): bool
  {
    return Session::get('auth') !== null;
  }

  public static function usuario(): ?array
  {
    return Session::get('auth');
  }
}
