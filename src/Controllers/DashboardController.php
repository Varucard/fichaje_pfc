<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Services\PromocionService;
use App\Services\StockService;

final class DashboardController extends Controller
{
  public function __construct(
    private readonly StockService $stock,
    private readonly PromocionService $promociones,
  ) {
  }

  public function inicio(Request $request): void
  {
    redirigir(Auth::check() ? '/dashboard' : '/login');
  }

  public function index(Request $request): void
  {
    $this->render('dashboard/index', [
      'titulo' => 'Panel del Administrador',
      'administrador' => Auth::usuario(),
      'stockBajo' => $this->stock->cantidadBajoMinimo(),
      'promociones' => $this->promociones->planesActivos(),
    ]);
  }
}
