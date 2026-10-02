<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\AuthService;

final class AuthController extends Controller
{
  public function __construct(private readonly AuthService $auth)
  {
  }

  public function mostrarLogin(Request $request): void
  {
    $this->render('auth/login', ['titulo' => 'Inicio de Sesión'], 'layouts/simple');
  }

  public function login(Request $request): void
  {
    $dni = (string) $request->input('dni', '');

    try {
      $usuario = $this->auth->autenticar($dni, (string) $request->input('password', ''));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/login', ['dni' => $dni]);
    }

    Auth::iniciarSesion($usuario);
    redirigir('/dashboard');
  }

  public function logout(Request $request): void
  {
    Auth::cerrarSesion();
    redirigir('/login');
  }
}
