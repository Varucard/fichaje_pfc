<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\AuditoriaService;
use App\Support\Log;

final class AuditoriaController extends Controller
{
  public function __construct(private readonly AuditoriaService $auditoria)
  {
  }

  /** Historial de acciones con filtros por fecha, tipo de acción y texto. */
  public function index(Request $request): void
  {
    $filtros = [
      'desde' => $this->fecha($request->query('desde')),
      'hasta' => $this->fecha($request->query('hasta')),
      'accion' => array_key_exists((string) $request->query('accion'), AuditoriaService::ACCIONES) ? $request->query('accion') : '',
      'texto' => (string) $request->query('texto', ''),
    ];
    $pagina = max(1, (int) $request->query('pagina', '1'));

    $this->render('sistema/auditoria', $this->auditoria->buscar($filtros, $pagina) + [
      'titulo' => 'Auditoría',
      'filtros' => $filtros,
      'pagina' => $pagina,
      'acciones' => AuditoriaService::ACCIONES,
    ]);
  }

  /** Visor de los logs técnicos (errores, accesos rechazados, etc.). */
  public function logs(Request $request): void
  {
    $nivel = array_key_exists((string) $request->query('nivel'), Log::NIVELES) ? $request->query('nivel') : 'info';
    $texto = (string) $request->query('texto', '');

    $this->render('sistema/logs', [
      'titulo' => 'Logs del sistema',
      'entradas' => Log::ultimas(200, $nivel, $texto),
      'nivel' => $nivel,
      'texto' => $texto,
      'niveles' => array_keys(Log::NIVELES),
    ]);
  }

  private function fecha(?string $valor): string
  {
    return $valor && \DateTimeImmutable::createFromFormat('!Y-m-d', $valor) ? $valor : '';
  }
}
