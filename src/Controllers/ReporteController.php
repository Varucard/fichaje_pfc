<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\ReporteService;

final class ReporteController extends Controller
{
  public function __construct(private readonly ReporteService $reportes)
  {
  }

  public function caja(Request $request): void
  {
    $anio = (int) $request->query('anio', date('Y'));
    $anio = $anio >= 2000 && $anio <= (int) date('Y') + 1 ? $anio : (int) date('Y');
    $mes = (int) $request->query('mes', '0');

    $this->render('reportes/caja', $this->reportes->caja($anio, $mes >= 1 && $mes <= 12 ? $mes : null) + [
      'titulo' => 'Reporte de caja',
    ]);
  }
}
