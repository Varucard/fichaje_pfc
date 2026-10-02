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
   * ¿La sesión sigue siendo válida según la base? Se cierra si el usuario fue
   * desactivado, dejó de ser administrador o se le cambió la contraseña.
   */
  public function sesionVigente(?array $sesion): bool
  {
    if ($sesion === null) {
      return false;
    }
    $usuario = $this->usuarios->buscarPorId((int) $sesion['id']);
    $vigente = $usuario !== null
      && (int) $usuario['asset'] === 1
      && !empty($usuario['password'])
      && hash_equals((string) ($sesion['huella'] ?? ''), \App\Core\Auth::huella($usuario));

    if (!$vigente) {
      \App\Core\Auth::cerrarSesion();
      \App\Core\Session::iniciar();
      \App\Core\Session::flash('aviso', 'Tu sesión se cerró porque cambiaron tus credenciales o tu usuario fue desactivado.');
    }
    return $vigente;
  }

  /** "30111222" o "30.111.222" => "30111222"; cualquier otro formato => null. */
  public static function normalizarDni(string $dni): ?string
  {
    $dni = str_replace(['.', ' '], '', trim($dni));
    if (!preg_match('/^\d{7,8}$/', $dni)) {
      return null;
    }
    return (string) (int) $dni;
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

    // Un DNI con formato inválido nunca se guarda tal cual (auditoría, logs, contador):
    // evita inyectar contenido en el panel y saltear el límite con variantes del mismo DNI.
    $dni = self::normalizarDni($dni);
    if ($dni === null) {
      $this->limite->fallo(null, $ip);
      $this->auditoria->registrar('sesion.fallida', 'Intento de inicio de sesión con un DNI de formato inválido', actor: 'Desconocido');
      throw new ValidacionException('Credenciales incorrectas.');
    }

    $minutos = $this->limite->bloqueo($dni, $ip);
    if ($minutos > 0) {
      throw new ValidacionException("Demasiados intentos fallidos. Probá de nuevo en {$minutos} minuto(s).");
    }
    // El intento se cuenta antes de comparar la contraseña (ver LimiteIntentosService).
    $intento = $this->limite->reservarIntento($dni, $ip);
    if ($intento['excedido']) {
      throw new ValidacionException('Demasiados intentos fallidos. Probá de nuevo en ' . (int) Config::get('login.bloqueo_minutos', 15) . ' minutos.');
    }

    $usuario = $this->usuarios->buscarPorDni($dni);
    $hash = $usuario['password'] ?? null;

    if (!password_verify($password, $hash ?: self::HASH_FICTICIO) || !$hash || !(int) $usuario['asset']) {
      $this->auditoria->registrar('sesion.fallida', "Intento de inicio de sesión fallido con el DNI {$dni}", 'usuario', $dni, actor: "DNI {$dni}");
      Log::warning('Inicio de sesión fallido', ['dni' => $dni]);
      if ($intento['bloqueado']) {
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
