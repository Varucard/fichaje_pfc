<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Domain\TipoUsuario;
use App\Exceptions\HttpException;
use App\Repositories\ReporteRepository;
use App\Services\AuditoriaService;
use App\Services\DeudaService;
use App\Services\LiquidacionService;
use App\Services\ReporteService;
use App\Services\UsuarioService;
use App\Support\Csv;
use DateTimeImmutable;

/**
 * Exportaciones a CSV (Excel). Cada descarga queda registrada en la auditoría,
 * porque los archivos contienen datos personales.
 */
final class ExportController extends Controller
{
  public function __construct(
    private readonly DeudaService $deudas,
    private readonly UsuarioService $usuarios,
    private readonly ReporteRepository $reportes,
    private readonly ReporteService $reporteService,
    private readonly AuditoriaService $auditoria,
  ) {
  }

  public function deudores(Request $request): void
  {
    $datos = $this->deudas->deudores();
    $this->registrar('deudores', count($datos['deudores']));
    Csv::enviar('deudores_' . date('Y-m-d') . '.csv',
      ['DNI', 'Apellido', 'Nombre', 'Email', 'Teléfono', 'Venció', 'Cuotas vencidas', 'Cuota mensual', 'Saldo parcial', 'Deuda total'],
      array_map(fn (array $a) => [
        (string) $a['dni'], $a['user_surname'], $a['user_name'], $a['email'], $a['phone_number'],
        $a['deuda']['renovacion'] ? fecha($a['deuda']['renovacion']) : 'Nunca pagó',
        $a['deuda']['meses'], (float) $a['deuda']['cuota'], (float) $a['deuda']['saldo_pagos'], (float) $a['deuda']['total'],
      ], $datos['deudores'])
    );
  }

  public function usuarios(Request $request, string $tipo): void
  {
    $tipoUsuario = match ($tipo) {
      'clientes' => TipoUsuario::Alumno,
      'profesores' => TipoUsuario::Profesor,
      default => throw new HttpException(404, 'Página no encontrada'),
    };
    $usuarios = $this->usuarios->listar($tipoUsuario);
    $this->registrar($tipo, count($usuarios));
    Csv::enviar("{$tipo}_" . date('Y-m-d') . '.csv',
      ['DNI', 'Apellido', 'Nombre', 'Llavero', 'Nacimiento', 'Email', 'Teléfono', 'Activo', 'Acepta avisos'],
      array_map(fn (array $u) => [
        (string) $u['dni'], $u['user_surname'], $u['user_name'], $u['rfid'],
        $u['birth_day'] ? fecha($u['birth_day']) : '', $u['email'], $u['phone_number'],
        $u['asset'] ? 'Sí' : 'No', ($u['acepta_emails'] ?? 1) ? 'Sí' : 'No',
      ], $usuarios)
    );
  }

  public function pagos(Request $request): void
  {
    $desde = $this->fecha($request->query('desde'), date('Y-m-01'));
    $hasta = $this->fecha($request->query('hasta'), date('Y-m-d'));
    $pagos = $this->reportes->pagosEntre($desde, $hasta);
    $this->registrar('pagos ' . $desde->format('d/m/Y') . ' al ' . $hasta->format('d/m/Y'), count($pagos));
    Csv::enviar('pagos_' . $desde->format('Y-m-d') . '_' . $hasta->format('Y-m-d') . '.csv',
      ['Comprobante', 'Fecha de pago', 'Vence', 'DNI', 'Apellido', 'Nombre', 'Clases', 'Meses', 'Promoción', 'Precio', 'Cobrado', 'Saldo'],
      array_map(fn (array $p) => [
        str_pad((string) $p['id_payment'], 8, '0', STR_PAD_LEFT), fecha($p['discharge_date']), fecha($p['date_of_renovation']),
        (string) $p['dni'], $p['user_surname'], $p['user_name'], $p['clases'], (int) $p['meses_cubiertos'], $p['promocion'],
        $p['monto_cuota'] !== null ? (float) $p['monto_cuota'] : null,
        $p['monto'] !== null ? (float) $p['monto'] : null,
        $p['monto'] !== null && $p['monto_cuota'] !== null ? max(0.0, (float) $p['monto_cuota'] - (float) $p['monto']) : null,
      ], $pagos)
    );
  }

  public function liquidaciones(Request $request): void
  {
    $periodo = LiquidacionService::validarPeriodo($request->query('periodo'));
    $filas = $this->reportes->liquidacionesDelPeriodo($periodo);
    $this->registrar("liquidaciones {$periodo}", count($filas));
    Csv::enviar("liquidaciones_{$periodo}.csv",
      ['Período', 'DNI', 'Apellido', 'Nombre', 'Modo', 'Base', 'Porcentaje', '$ por asistencia', 'Monto', 'Estado', 'Fecha de pago'],
      array_map(fn (array $l) => [
        $l['periodo'], (string) $l['dni'], $l['user_surname'], $l['user_name'],
        $l['modo'] === 'asistencia' ? 'Por asistencia' : '% de lo cobrado',
        (float) $l['monto_base'],
        $l['porcentaje'] !== null ? (float) $l['porcentaje'] : null,
        $l['monto_por_asistencia'] !== null ? (float) $l['monto_por_asistencia'] : null,
        (float) $l['monto'], $l['pagada'] ? 'Pagada' : 'Pendiente', $l['fecha_pago'] ? fecha($l['fecha_pago']) : '',
      ], $filas)
    );
  }

  public function caja(Request $request): void
  {
    $anio = (int) $request->query('anio', date('Y'));
    $reporte = $this->reporteService->caja($anio >= 2000 ? $anio : (int) date('Y'));
    $this->registrar("caja {$reporte['anio']}", 12);
    Csv::enviar("caja_{$reporte['anio']}.csv",
      ['Mes', 'Pagos', 'Cuotas', 'Ventas', 'Total', 'Variación vs. año anterior (%)'],
      array_map(fn (array $m) => [
        $m['nombre'] . ' ' . $reporte['anio'], $m['pagos'], (float) $m['cuotas'], (float) $m['ventas'], (float) $m['total'],
        $m['variacion'] !== null ? (float) $m['variacion'] : null,
      ], $reporte['meses'])
    );
  }

  private function registrar(string $que, int $filas): void
  {
    $this->auditoria->registrar('sistema.exportacion', "Exportación a CSV: {$que} ({$filas} fila/s)");
  }

  private function fecha(?string $valor, string $defecto): DateTimeImmutable
  {
    return fecha_valida($valor) ?? new DateTimeImmutable($defecto);
  }
}
