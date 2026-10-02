<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
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
    private readonly LimiteIntentosService $limite,
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

    $ip = $_SERVER['REMOTE_ADDR'] ?? 'cli';
    $minutos = $this->limite->bloqueo($dni, $ip);
    if ($minutos > 0) {
      throw new ValidacionException("Demasiados intentos fallidos. Probá de nuevo en {$minutos} minuto(s).");
    }

    $usuario = $this->usuarios->buscarPorDni($dni);
    $hash = $usuario['password'] ?? null;

    if (!password_verify($password, $hash ?: self::HASH_FICTICIO) || !$hash || !(int) $usuario['asset']) {
      $this->auditoria->registrar('sesion.fallida', "Intento de inicio de sesión fallido con el DNI {$dni}", 'usuario', $dni, actor: "DNI {$dni}");
      Log::warning('Inicio de sesión fallido', ['dni' => $dni]);
      if ($this->limite->fallo($dni, $ip)) {
        $this->auditoria->registrar('sesion.bloqueo', "Inicio de sesión bloqueado temporalmente por intentos fallidos (DNI {$dni}, IP {$ip})", 'usuario', $dni, actor: "DNI {$dni}");
        Log::warning('Inicio de sesión bloqueado por intentos fallidos', ['dni' => $dni]);
        throw new ValidacionException('Demasiados intentos fallidos. El acceso quedó bloqueado por ' . (int) Config::get('login.bloqueo_minutos', 15) . ' minutos.');
      }
      throw new ValidacionException('Credenciales incorrectas.');
    }

    if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
      $this->usuarios->actualizarPassword((int) $usuario['id_user'], password_hash($password, PASSWORD_DEFAULT));
    }

    $this->limite->exito($dni, $ip);
    $this->auditoria->registrar('sesion.inicio', "Inicio de sesión de {$usuario['user_name']}", 'usuario', $dni, actor: $usuario['user_name']);
    return $usuario;
  }
}
