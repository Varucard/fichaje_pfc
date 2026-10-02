<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidacionException;
use App\Repositories\UsuarioRepository;
use App\Support\Log;

final class AuthService
{
  // Hash ficticio para que el tiempo de respuesta no revele si el DNI existe.
  private const HASH_FICTICIO = '$2y$10$30Z2dKs8oSrAQAK1ju2PyOeMljaN9KhjlN982KdCR4fyMp7i5Tie2';

  public function __construct(
    private readonly UsuarioRepository $usuarios,
    private readonly AuditoriaService $auditoria,
  ) {
  }

  /**
   * Valida las credenciales y devuelve el usuario.
   * Solo pueden ingresar usuarios activos que tengan contraseña asignada.
   */
  public function autenticar(string $dni, string $password): array
  {
    if ($dni === '' || $password === '') {
      throw new ValidacionException('Por favor, complete todos los campos.');
    }

    $usuario = $this->usuarios->buscarPorDni($dni);
    $hash = $usuario['password'] ?? null;

    if (!password_verify($password, $hash ?: self::HASH_FICTICIO) || !$hash || !(int) $usuario['asset']) {
      $this->auditoria->registrar('sesion.fallida', "Intento de inicio de sesión fallido con el DNI {$dni}", 'usuario', $dni, actor: "DNI {$dni}");
      Log::warning('Inicio de sesión fallido', ['dni' => $dni]);
      throw new ValidacionException('Credenciales incorrectas.');
    }

    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
      $this->usuarios->actualizarPassword((int) $usuario['id_user'], password_hash($password, PASSWORD_DEFAULT));
    }

    $this->auditoria->registrar('sesion.inicio', "Inicio de sesión de {$usuario['user_name']}", 'usuario', $dni, actor: $usuario['user_name']);
    return $usuario;
  }
}
