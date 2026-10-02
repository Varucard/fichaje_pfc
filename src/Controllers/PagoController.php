<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\ComprobanteService;
use App\Services\PagoService;
use DateTimeImmutable;

final class PagoController extends Controller
{
  public function __construct(
    private readonly PagoService $pagos,
    private readonly ComprobanteService $comprobantes,
  ) {
  }

  /** Comprobante de pago en PDF (se abre en el navegador para imprimir o descargar). */
  public function comprobante(Request $request, string $id): void
  {
    try {
      [$archivo, $pdf] = $this->comprobantes->pdf((int) $id);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/dashboard');
    }
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . $archivo . '"');
    header('Content-Length: ' . strlen($pdf));
    echo $pdf;
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
      $usuario = $this->pagos->registrarPorDni((string) $request->input('dni', ''), $fecha, $this->monto($request));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), $volver);
    }

    $this->exito('Pago registrado exitosamente.', '/usuarios/' . $usuario['dni']);
  }

  /** Monto opcional del formulario ("12.500,50" o "12500.50"). Vacío = cuota completa. */
  private function monto(Request $request): ?float
  {
    $valor = str_replace(['$', ' '], '', (string) $request->input('monto', ''));
    if ($valor === '') {
      return null;
    }
    if (str_contains($valor, ',')) {
      $valor = str_replace(['.', ','], ['', '.'], $valor);
    }
    if (!is_numeric($valor)) {
      throw new ValidacionException('El monto no es válido.');
    }
    return (float) $valor;
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
