<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;

final class DashboardController extends Controller
{
  public function inicio(Request $request): void
  {
    redirigir(Auth::check() ? '/dashboard' : '/login');
  }

  public function index(Request $request): void
  {
    $this->render('dashboard/index', [
      'titulo' => 'Panel del Administrador',
      'administrador' => Auth::usuario(),
    ]);
  }
}
