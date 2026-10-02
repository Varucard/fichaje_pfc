<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Domain\Llavero;
use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Repositories\UsuarioRepository;

/**
 * Administradores del panel: alta, contraseñas y activación.
 * Nunca se puede quedar el sistema sin un administrador activo.
 */
final class AdministradorService
{
  public const LARGO_MINIMO = 8;

  public function __construct(
    private readonly UsuarioRepository $usuarios,
    private readonly AuditoriaService $auditoria,
  ) {
  }

  /** Regla de contraseñas (función pura, cubierta por tests). Devuelve el error o null. */
  public static function validarPassword(string $password, string $dni): ?string
  {
    if (mb_strlen($password) < self::LARGO_MINIMO) {
      return 'La contraseña debe tener al menos ' . self::LARGO_MINIMO . ' caracteres.';
    }
    if ($password === $dni || ctype_digit($password) && strlen(count_chars($password, 3)) === 1) {
      return 'La contraseña es demasiado fácil de adivinar.';
    }
    return null;
  }

  public function listar(): array
  {
    return $this->usuarios->listarPorTipo(TipoUsuario::Administrador);
  }

  public function crear(array $entrada): array
  {
    $dni = preg_replace('/\D/', '', (string) ($entrada['dni'] ?? ''));
    $nombre = UsuarioService::capitalizar($entrada['nombre'] ?? '');
    $password = (string) ($entrada['password'] ?? '');

    if (strlen($dni) < 7 || strlen($dni) > 8 || $nombre === '') {
      throw new ValidacionException('Ingresá un DNI de 7 u 8 dígitos y el nombre.');
    }
    if ($this->usuarios->buscarPorDni($dni)) {
      throw new ValidacionException('Ya existe un usuario con ese DNI.');
    }
    $this->exigirPassword($password, (string) ($entrada['password2'] ?? ''), $dni);

    $email = trim((string) ($entrada['email'] ?? ''));
    if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      throw new ValidacionException('El email no es válido.');
    }

    return $this->usuarios->transaccion(function () use ($dni, $nombre, $entrada, $email, $password): array {
      $id = $this->usuarios->crear([
        'rfid' => Llavero::SIN_LLAVERO,
        'dni' => $dni,
        'nombre' => $nombre,
        'apellido' => UsuarioService::capitalizar($entrada['apellido'] ?? ''),
        'nacimiento' => '',
        'email' => $email,
        'telefono' => '',
        'tipo' => TipoUsuario::Administrador,
      ]);
      $this->usuarios->actualizarPassword($id, password_hash($password, PASSWORD_DEFAULT));
      $this->auditoria->registrar('usuario.admin_alta', "Alta del administrador {$nombre} (DNI {$dni})", 'usuario', $dni);
      return $this->usuarios->buscarPorId($id);
    });
  }

  /** Un administrador cambia la contraseña de otro (o la propia, sin pedir la actual). */
  public function cambiarPassword(int $id, string $password, string $confirmacion): void
  {
    $admin = $this->admin($id);
    $this->exigirPassword($password, $confirmacion, (string) $admin['dni']);
    $this->usuarios->actualizarPassword($id, password_hash($password, PASSWORD_DEFAULT));
    $this->auditoria->registrar('usuario.admin_password', "Cambio de contraseña del administrador {$admin['user_name']}", 'usuario', $admin['dni']);
  }

  /** Cambio de la propia contraseña: exige la actual. */
  public function cambiarMiPassword(string $actual, string $password, string $confirmacion): void
  {
    $id = (int) (Auth::usuario()['id'] ?? 0);
    $admin = $this->usuarios->buscarPorId($id) ?? throw new ValidacionException('Sesión inválida.');
    if (!password_verify($actual, (string) $admin['password'])) {
      throw new ValidacionException('La contraseña actual no es correcta.');
    }
    $this->exigirPassword($password, $confirmacion, (string) $admin['dni']);
    $this->usuarios->actualizarPassword($id, password_hash($password, PASSWORD_DEFAULT));
    Auth::iniciarSesion($this->usuarios->buscarPorId($id));
    $this->auditoria->registrar('usuario.admin_password', "{$admin['user_name']} cambió su contraseña", 'usuario', $admin['dni']);
  }

  public function cambiarActivo(int $id, bool $activo): array
  {
    $admin = $this->admin($id);
    if (!$activo) {
      if ($id === (int) (Auth::usuario()['id'] ?? 0)) {
        throw new ValidacionException('No podés desactivar tu propio usuario.');
      }
      $activos = array_filter($this->listar(), fn (array $a) => (int) $a['asset'] && $a['password']);
      if (count($activos) <= 1) {
        throw new ValidacionException('No se puede desactivar al último administrador activo.');
      }
    }
    $this->usuarios->cambiarEstado($id, $activo);
    $this->auditoria->registrar(
      $activo ? 'usuario.admin_reactivacion' : 'usuario.admin_desactivacion',
      ($activo ? 'Reactivación' : 'Desactivación') . " del administrador {$admin['user_name']}",
      'usuario',
      $admin['dni']
    );
    return $admin;
  }

  private function admin(int $id): array
  {
    $admin = $this->usuarios->buscarPorId($id);
    if (!$admin || TipoUsuario::deUsuario($admin) !== TipoUsuario::Administrador) {
      throw new ValidacionException('El administrador no existe.');
    }
    return $admin;
  }

  private function exigirPassword(string $password, string $confirmacion, string $dni): void
  {
    if ($password !== $confirmacion) {
      throw new ValidacionException('Las contraseñas no coinciden.');
    }
    $error = self::validarPassword($password, $dni);
    if ($error !== null) {
      throw new ValidacionException($error);
    }
  }
}
