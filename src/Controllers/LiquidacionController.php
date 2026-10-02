<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\LiquidacionService;

final class LiquidacionController extends Controller
{
  public function __construct(private readonly LiquidacionService $liquidaciones)
  {
  }

  public function index(Request $request): void
  {
    $resumen = $this->liquidaciones->resumen((string) $request->query('periodo', ''));

    $this->render('liquidaciones/index', $resumen + [
      'titulo' => 'Liquidación de Profesores',
      'resaltar' => (string) $request->query('profesor', ''),
    ]);
  }

  public function registrar(Request $request): void
  {
    $periodo = LiquidacionService::validarPeriodo($request->input('periodo'));

    try {
      $calculo = $this->liquidaciones->registrar((int) $request->input('id_profesor', '0'), $periodo);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $this->volver($periodo));
    }

    $this->exito('Liquidación registrada por ' . dinero($calculo['monto']) . '.', $this->volver($periodo));
  }

  public function pagar(Request $request, string $id): void
  {
    try {
      $liquidacion = $this->liquidaciones->marcarPagada((int) $id);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $this->volver($request->input('periodo')));
    }

    $this->exito('Liquidación marcada como pagada.', $this->volver($liquidacion['periodo']));
  }

  public function anular(Request $request, string $id): void
  {
    try {
      $liquidacion = $this->liquidaciones->anular((int) $id);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $this->volver($request->input('periodo')));
    }

    $this->exito('Liquidación anulada.', $this->volver($liquidacion['periodo']));
  }

  public function porcentaje(Request $request, string $id): void
  {
    $valor = trim(str_replace(['%', ','], ['', '.'], (string) $request->input('porcentaje', '')));
    $periodo = $request->input('periodo');

    try {
      if ($valor !== '' && !is_numeric($valor)) {
        throw new ValidacionException('El porcentaje no es válido.');
      }
      $this->liquidaciones->actualizarPorcentaje((int) $id, $valor === '' ? null : (float) $valor);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $this->volver($periodo));
    }

    $this->exito('Porcentaje actualizado.', $this->volver($periodo));
  }

  private function volver(?string $periodo): string
  {
    return '/liquidaciones?periodo=' . LiquidacionService::validarPeriodo($periodo);
  }
}
