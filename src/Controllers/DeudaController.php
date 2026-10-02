<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Services\DeudaService;

final class DeudaController extends Controller
{
  public function __construct(private readonly DeudaService $deudas)
  {
  }

  public function index(Request $request): void
  {
    $this->render('deudas/lista', $this->deudas->deudores() + ['titulo' => 'Deudores']);
  }
}
