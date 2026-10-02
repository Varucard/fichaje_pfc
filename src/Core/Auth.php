<?php

declare(strict_types=1);

namespace App\Core;

final class Auth
{
  public static function iniciarSesion(array $usuario): void
  {
    Session::regenerar();
    Session::set('auth', [
      'id' => (int) $usuario['id_user'],
      'nombre' => $usuario['user_name'],
      'dni' => (string) $usuario['dni'],
    ]);
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
