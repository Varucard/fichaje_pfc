<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\PagoService;
use DateTimeImmutable;

final class PagoController extends Controller
{
  public function __construct(private readonly PagoService $pagos)
  {
  }

  /** "Renovar pago": registra un pago con fecha de hoy. */
  public function renovar(Request $request, string $dni): void
  {
    try {
      $usuario = $this->pagos->registrarPorDni($dni);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $request->input('volver', '/dashboard'));
    }

    $this->exito('Pago registrado exitosamente.', '/usuarios/' . $usuario['dni']);
  }

  /** Pago con fecha elegida, desde la ficha del usuario o desde el panel (por DNI). */
  public function manual(Request $request): void
  {
    $volver = $request->input('volver', '/dashboard');
    $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', (string) $request->input('fecha', ''));

    try {
      if ($fecha === false) {
        throw new ValidacionException('La fecha de pago no es válida.');
      }
      $usuario = $this->pagos->registrarPorDni((string) $request->input('dni', ''), $fecha);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $volver);
    }

    $this->exito('Pago registrado exitosamente.', '/usuarios/' . $usuario['dni']);
  }

  public function eliminar(Request $request, string $idPago): void
  {
    try {
      $usuario = $this->pagos->eliminar((int) $idPago);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $request->input('volver', '/dashboard'));
    }

    $this->exito('Pago eliminado exitosamente.', '/usuarios/' . ($usuario['dni'] ?? ''));
  }
}
