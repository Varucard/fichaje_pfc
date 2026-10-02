<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\TipoUsuario;
use App\Repositories\MatriculaRepository;
use App\Repositories\PagoRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

/**
 * Deuda de los alumnos.
 *
 * deuda = (meses vencidos × cuota mensual actual) + saldos de pagos parciales
 *
 * - Cuota mensual: suma de los precios de las clases del alumno.
 * - Meses vencidos: períodos mensuales iniciados desde el último vencimiento (la cuota
 *   vale hasta el día del vencimiento inclusive). Si se lo reactivó después de ese
 *   vencimiento, se cuenta desde la reactivación. Un alumno con clases que nunca pagó
 *   adeuda 1 mes.
 * - Saldo de pagos parciales: lo que faltó cobrar en pagos menores a la cuota.
 */
final class DeudaService
{
  public function __construct(
    private readonly UsuarioRepository $usuarios,
    private readonly PagoRepository $pagos,
    private readonly MatriculaRepository $matriculas,
  ) {
  }

  /**
   * Cantidad de períodos mensuales vencidos e impagos.
   * Ej: vencimiento 10/05 → el 11/05 debe 1 mes; el 11/06, 2 meses.
   */
  public static function mesesVencidos(
    ?string $renovacion,
    DateTimeImmutable $ahora,
    ?int $diaAncla = null,
    ?string $cuotaDesde = null,
  ): int {
    $hoy = $ahora->format('Y-m-d');

    // Reactivado después del vencimiento: debe desde el día de la reactivación.
    if ($cuotaDesde !== null && ($renovacion === null || $cuotaDesde > $renovacion)) {
      $meses = 0;
      $inicio = new DateTimeImmutable($cuotaDesde);
      while ($hoy >= PagoService::sumarMeses($inicio, $meses)->format('Y-m-d') && $meses < 120) {
        $meses++;
      }
      return $meses;
    }

    if ($renovacion === null) {
      return 1;
    }

    $meses = 0;
    $vencimiento = new DateTimeImmutable($renovacion);
    $ancla = $diaAncla ?? (int) $vencimiento->format('d');
    while ($hoy > $vencimiento->format('Y-m-d') && $meses < 120) {
      $meses++;
      $vencimiento = PagoService::sumarMeses($vencimiento, 1, $ancla);
    }
    return $meses;
  }

  /** @return array{cuota: float, meses: int, saldo_pagos: float, total: float, renovacion: ?string} */
  public static function calcular(
    float $cuota,
    ?string $renovacion,
    float $saldoPagos,
    DateTimeImmutable $ahora,
    ?int $diaAncla = null,
    ?string $cuotaDesde = null,
  ): array {
    $meses = $cuota > 0 ? self::mesesVencidos($renovacion, $ahora, $diaAncla, $cuotaDesde) : 0;
    return [
      'cuota' => $cuota,
      'meses' => $meses,
      'saldo_pagos' => round($saldoPagos, 2),
      'total' => round($meses * $cuota + $saldoPagos, 2),
      'renovacion' => $renovacion,
    ];
  }

  /** Deuda de un alumno (para su ficha). */
  public function deudaDe(int $idUsuario): array
  {
    $ultimo = $this->pagos->ultimoPago($idUsuario);
    return self::calcular(
      PagoService::cuota($this->matriculas->clasesDeAlumno($idUsuario)),
      $ultimo['renovacion'] ?? null,
      $this->pagos->saldoPendiente($idUsuario),
      new DateTimeImmutable(),
      $ultimo['dia_ancla'] ?? null,
      $this->usuarios->buscarPorId($idUsuario)['cuota_desde'] ?? null,
    );
  }

  /**
   * Alumnos activos con deuda, de mayor a menor.
   *
   * @return array{deudores: array, total: float}
   */
  public function deudores(): array
  {
    $cuotas = $this->matriculas->cuotasPorAlumno();
    $resumen = $this->pagos->resumenPorAlumno();
    $ahora = new DateTimeImmutable();

    $deudores = [];
    foreach ($this->usuarios->listarPorTipo(TipoUsuario::Alumno, soloActivos: true) as $alumno) {
      $id = (int) $alumno['id_user'];
      $deuda = self::calcular(
        $cuotas[$id] ?? 0.0,
        $resumen[$id]['renovacion'] ?? null,
        $resumen[$id]['saldo'] ?? 0.0,
        $ahora,
        $resumen[$id]['dia_ancla'] ?? null,
        $alumno['cuota_desde'] ?? null,
      );
      if ($deuda['total'] > 0) {
        $deudores[] = $alumno + ['deuda' => $deuda];
      }
    }

    usort($deudores, fn (array $a, array $b) => $b['deuda']['total'] <=> $a['deuda']['total']);

    return [
      'deudores' => $deudores,
      'total' => round(array_sum(array_map(fn ($d) => $d['deuda']['total'], $deudores)), 2),
    ];
  }
}
