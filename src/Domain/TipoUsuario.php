<?php

declare(strict_types=1);

namespace App\Domain;

/**
 * Valores de la tabla types_users.
 */
enum TipoUsuario: int
{
  case Profesor = 1;
  case Alumno = 2;
  case Administrador = 3;

  public function etiqueta(): string
  {
    return match ($this) {
      self::Profesor => 'Profesor',
      self::Alumno => 'Cliente',
      self::Administrador => 'Administrador',
    };
  }

  public static function deUsuario(array $usuario): self
  {
    return self::from((int) $usuario['type_user']);
  }
}
