<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Request;
use App\Repositories\LlaveroPendienteRepository;
use App\Services\FichajeService;
use App\Services\UsuarioService;

/**
 * Endpoints JSON que consulta el panel periódicamente (requieren sesión iniciada).
 */
final class PanelApiController extends Controller
{
  public function __construct(
    private readonly FichajeService $fichajes,
    private readonly UsuarioService $usuarios,
    private readonly LlaveroPendienteRepository $pendientes,
  ) {
  }

  public function ultimosFichajes(Request $request): void
  {
    $this->json($this->fichajes->ultimos());
  }

  public function cumpleaneros(Request $request): void
  {
    $this->json($this->usuarios->cumpleanerosDeHoy());
  }

  public function llaverosPendientes(Request $request): void
  {
    $this->json(['status' => 'ok', 'llaveros' => $this->pendientes->tomarPendientes()]);
  }
}
