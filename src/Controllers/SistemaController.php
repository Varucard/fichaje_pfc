<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\SistemaService;

final class SistemaController extends Controller
{
  public function __construct(private readonly SistemaService $sistema)
  {
  }

  public function reiniciarArduino(Request $request): void
  {
    try {
      $this->sistema->reiniciarArduino();
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/dashboard');
    }

    $this->exito('Reiniciando Arduino. Por favor aguarde...', '/dashboard');
  }

  public function backup(Request $request): void
  {
    try {
      $archivo = $this->sistema->generarBackup();
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/dashboard');
    }

    $this->exito('Respaldo generado correctamente: ' . basename($archivo), '/dashboard');
  }
}
