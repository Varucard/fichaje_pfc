<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\FichajeService;

final class FichajeController extends Controller
{
  public function __construct(private readonly FichajeService $fichajes)
  {
  }

  /** Últimos ingresos: la tabla se actualiza sola vía /api/fichajes/ultimos. */
  public function index(Request $request): void
  {
    $this->render('fichajes/ultimos', ['titulo' => 'Últimos Ingresos']);
  }

  public function buscar(Request $request): void
  {
    $termino = (string) $request->query('q', '');

    $this->render('fichajes/buscar', [
      'titulo' => 'Búsqueda de Fichajes',
      'termino' => $termino,
      'fichajes' => $termino === '' ? [] : $this->fichajes->buscar($termino),
    ]);
  }

  public function manual(Request $request): void
  {
    $volver = $request->input('volver', '/fichajes');

    try {
      $usuario = $this->fichajes->registrarManual((string) $request->input('dni', ''));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $volver);
    }

    $this->exito("Fichada registrada: {$usuario['user_name']} {$usuario['user_surname']}.", $volver);
  }
}
