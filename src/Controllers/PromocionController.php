<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\PromocionService;

final class PromocionController extends Controller
{
  public function __construct(private readonly PromocionService $promociones)
  {
  }

  public function index(Request $request): void
  {
    $this->render('promociones/index', [
      'titulo' => 'Promociones',
      'promociones' => $this->promociones->listar(),
    ]);
  }

  public function guardar(Request $request): void
  {
    $entrada = [];
    foreach (['nombre', 'meses_pagos', 'meses_bonificados', 'descuento'] as $campo) {
      $entrada[$campo] = (string) $request->input($campo, '');
    }

    try {
      $this->promociones->crear($entrada);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/promociones', $entrada);
    }
    $this->exito('Promoción creada.', '/promociones');
  }

  public function cambiarActiva(Request $request, string $id): void
  {
    $activa = $request->input('activa') === '1';
    try {
      $this->promociones->cambiarActiva((int) $id, $activa);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/promociones');
    }
    $this->exito($activa ? 'Promoción activada.' : 'Promoción desactivada.', '/promociones');
  }
}
