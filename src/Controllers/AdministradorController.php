<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\AdministradorService;

final class AdministradorController extends Controller
{
  public function __construct(private readonly AdministradorService $admins)
  {
  }

  public function index(Request $request): void
  {
    $this->render('administradores/index', [
      'titulo' => 'Administradores',
      'administradores' => $this->admins->listar(),
      'yo' => (int) (Auth::usuario()['id'] ?? 0),
    ]);
  }

  public function guardar(Request $request): void
  {
    $entrada = [];
    foreach (['dni', 'nombre', 'apellido', 'email', 'password', 'password2'] as $campo) {
      $entrada[$campo] = (string) $request->input($campo, '');
    }
    try {
      $admin = $this->admins->crear($entrada);
    } catch (ValidacionException $e) {
      unset($entrada['password'], $entrada['password2']);
      $this->error($e->getMessage(), '/administradores', $entrada);
    }
    $this->exito("Administrador {$admin['user_name']} creado. Ya puede ingresar con su DNI.", '/administradores');
  }

  public function password(Request $request, string $id): void
  {
    try {
      $this->admins->cambiarPassword((int) $id, (string) $request->input('password', ''), (string) $request->input('password2', ''));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/administradores');
    }
    $this->exito('Contraseña actualizada.', '/administradores');
  }

  public function cambiarActivo(Request $request, string $id): void
  {
    $activo = $request->input('activo') === '1';
    try {
      $this->admins->cambiarActivo((int) $id, $activo);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/administradores');
    }
    $this->exito($activo ? 'Administrador reactivado.' : 'Administrador desactivado.', '/administradores');
  }

  public function miCuenta(Request $request): void
  {
    $this->render('administradores/mi-cuenta', ['titulo' => 'Mi cuenta', 'administrador' => Auth::usuario()]);
  }

  public function miPassword(Request $request): void
  {
    try {
      $this->admins->cambiarMiPassword(
        (string) $request->input('actual', ''),
        (string) $request->input('password', ''),
        (string) $request->input('password2', '')
      );
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/mi-cuenta');
    }
    $this->exito('Tu contraseña se cambió correctamente.', '/mi-cuenta');
  }
}
