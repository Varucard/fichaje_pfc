<?php

declare(strict_types=1);

namespace App\Services;

use App\Repositories\ReporteRepository;
use DateTimeImmutable;

/**
 * Reporte de caja: lo cobrado en cuotas y ventas de productos, por mes y por clase.
 * Criterio de caja: cada cobro cuenta en el mes en que se cobró (un pago de 3 meses
 * suma completo en su mes; la liquidación de profesores sí lo reparte).
 */
final class ReporteService
{
  public const MESES = [1 => 'Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

  public function __construct(private readonly ReporteRepository $reportes)
  {
  }

  /** Variación porcentual entre dos totales (null si no hay base de comparación). */
  public static function variacion(float $actual, float $anterior): ?float
  {
    return $anterior > 0 ? round(($actual - $anterior) / $anterior * 100, 1) : null;
  }

  public function caja(int $anio, ?int $mesDetalle = null): array
  {
    $hoy = new DateTimeImmutable('today');
    $cuotas = $this->reportes->cuotasPorMes($anio);
    $ventas = $this->reportes->ventasPorMes($anio);
    $cuotasAnterior = $this->reportes->cuotasPorMes($anio - 1);
    $ventasAnterior = $this->reportes->ventasPorMes($anio - 1);

    $meses = [];
    $totales = ['cuotas' => 0.0, 'ventas' => 0.0, 'total' => 0.0, 'pagos' => 0, 'anterior' => 0.0, 'cerrados' => 0.0];
    $ultimoMes = $anio < (int) $hoy->format('Y') ? 12 : ($anio > (int) $hoy->format('Y') ? 0 : (int) $hoy->format('n'));
    for ($mes = 1; $mes <= 12; $mes++) {
      $fila = [
        'mes' => $mes,
        'nombre' => self::MESES[$mes],
        'cuotas' => $cuotas[$mes]['total'] ?? 0.0,
        'pagos' => $cuotas[$mes]['cantidad'] ?? 0,
        'ventas' => $ventas[$mes]['total'] ?? 0.0,
        'futuro' => $mes > $ultimoMes,
      ];
      $fila['total'] = $fila['cuotas'] + $fila['ventas'];
      $anterior = ($cuotasAnterior[$mes]['total'] ?? 0.0) + ($ventasAnterior[$mes]['total'] ?? 0.0);
      $fila['en_curso'] = $anio === (int) $hoy->format('Y') && $mes === (int) $hoy->format('n');
      // El mes en curso está incompleto: compararlo con un mes entero sería engañoso.
      $fila['variacion'] = $fila['futuro'] || $fila['en_curso'] ? null : self::variacion($fila['total'], $anterior);
      $meses[] = $fila;

      $totales['cuotas'] += $fila['cuotas'];
      $totales['ventas'] += $fila['ventas'];
      $totales['total'] += $fila['total'];
      $totales['pagos'] += $fila['pagos'];
      if (!$fila['futuro'] && !$fila['en_curso']) {
        $totales['anterior'] += $anterior;
        $totales['cerrados'] += $fila['total'];
      }
    }
    // Variación anual sobre los meses cerrados (sin el mes en curso).
    $totales['variacion'] = self::variacion($totales['cerrados'], $totales['anterior']);

    $mesDetalle ??= $ultimoMes ?: 12;
    $desde = new DateTimeImmutable(sprintf('%04d-%02d-01', $anio, $mesDetalle));
    $hasta = $desde->modify('last day of this month');

    return [
      'anio' => $anio,
      'meses' => $meses,
      'totales' => $totales,
      'maximo' => max(array_column($meses, 'total') ?: [0]),
      'mes_detalle' => $mesDetalle,
      'por_clase' => $this->reportes->cuotasPorClase($desde, $hasta),
      'sin_clase' => $this->reportes->cuotasSinClase($desde, $hasta),
      'por_producto' => $this->reportes->ventasPorProducto($desde, $hasta),
    ];
  }
}
