<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Migrador;
use App\Services\DeudaService;
use App\Services\PagoService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class DeudaTest extends TestCase
{
  private const CLASES = [
    ['id_class' => 1, 'name_class' => 'Boxeo', 'price_class' => 12000],
    ['id_class' => 2, 'name_class' => 'Kick Boxing', 'price_class' => 10000],
  ];

  public function testLaCuotaEsLaSumaDeLasClases(): void
  {
    self::assertSame(22000.0, PagoService::cuota(self::CLASES));
    self::assertSame(0.0, PagoService::cuota([]));
  }

  public function testElPagoSeRepartePorPrecioYSumaExacto(): void
  {
    $partes = PagoService::prorratear(22000, self::CLASES);
    self::assertSame([12000.0, 10000.0], array_column($partes, 'monto'));

    $partes = PagoService::prorratear(10000, self::CLASES);
    self::assertSame(5454.55, $partes[0]['monto']);
    self::assertSame(10000.0, round(array_sum(array_column($partes, 'monto')), 2), 'El redondeo no debe perder centavos');
  }

  public function testElRepartoNuncaDaPartesNegativas(): void
  {
    $clases = [
      ['id_class' => 1, 'name_class' => 'A', 'price_class' => 1],
      ['id_class' => 2, 'name_class' => 'B', 'price_class' => 1],
      ['id_class' => 3, 'name_class' => 'C', 'price_class' => 0],
    ];
    $partes = PagoService::prorratear(100.01, $clases);

    self::assertGreaterThanOrEqual(0, min(array_column($partes, 'monto')));
    self::assertSame(100.01, round(array_sum(array_column($partes, 'monto')), 2));
  }

  public function testFechasYMontosEscritosAMano(): void
  {
    self::assertNull(fecha_valida('2025-02-30'), 'Fecha imposible');
    self::assertNull(fecha_valida('2025-13-01'));
    self::assertSame('2024-02-29', fecha_valida('2024-02-29')?->format('Y-m-d'));

    self::assertSame(12500.0, monto_desde_texto('12.500'));
    self::assertSame(12500.5, monto_desde_texto('$ 12.500,50'));
    self::assertSame(12500.5, monto_desde_texto('12500.50'));
    self::assertSame(12.5, monto_desde_texto('12,5'));
    self::assertNull(monto_desde_texto('doce'));
  }

  public function testSinClasesNoHayReparto(): void
  {
    self::assertSame([], PagoService::prorratear(5000, []));
  }

  public function testMesesVencidos(): void
  {
    $vence = '2025-05-10';
    self::assertSame(0, DeudaService::mesesVencidos($vence, new DateTimeImmutable('2025-05-09 12:00')));
    self::assertSame(0, DeudaService::mesesVencidos($vence, new DateTimeImmutable('2025-05-10 22:00')), 'El día del vencimiento no debe nada');
    self::assertSame(1, DeudaService::mesesVencidos($vence, new DateTimeImmutable('2025-05-11 08:00')));
    self::assertSame(1, DeudaService::mesesVencidos($vence, new DateTimeImmutable('2025-06-09 23:00')));
    self::assertSame(2, DeudaService::mesesVencidos($vence, new DateTimeImmutable('2025-06-11 08:00')));
    self::assertSame(1, DeudaService::mesesVencidos(null, new DateTimeImmutable('2025-06-11')), 'Nunca pagó: debe un mes');
  }

  public function testMesesVencidosConDiaAncla(): void
  {
    // Ancla 31: vence 28/02 y el próximo vencimiento es 31/03 (no 28/03).
    self::assertSame(1, DeudaService::mesesVencidos('2025-02-28', new DateTimeImmutable('2025-03-30 12:00'), 31));
    self::assertSame(2, DeudaService::mesesVencidos('2025-02-28', new DateTimeImmutable('2025-04-01'), 31));
  }

  public function testReactivadoNoDebeElTiempoInactivo(): void
  {
    // Último vencimiento 10/01; reactivado el 05/06: el 06/06 debe 1 cuota, no 5.
    self::assertSame(1, DeudaService::mesesVencidos('2025-01-10', new DateTimeImmutable('2025-06-06'), null, '2025-06-05'));
    self::assertSame(2, DeudaService::mesesVencidos('2025-01-10', new DateTimeImmutable('2025-07-05'), null, '2025-06-05'));
  }

  public function testDeudaTotalSumaMesesYSaldosParciales(): void
  {
    $deuda = DeudaService::calcular(22000, '2025-05-10', 2000, new DateTimeImmutable('2025-06-11'));

    self::assertSame(2, $deuda['meses']);
    self::assertSame(46000.0, $deuda['total']);
  }

  public function testAlumnoSinClasesNoAcumulaCuotas(): void
  {
    $deuda = DeudaService::calcular(0, null, 0, new DateTimeImmutable('2025-06-11'));

    self::assertSame(0, $deuda['meses']);
    self::assertSame(0.0, $deuda['total']);
  }

  public function testElMigradorSeparaSentenciasEIgnoraComentarios(): void
  {
    $sql = "-- comentario; con punto y coma\nALTER TABLE a ADD b INT;\n\nCREATE TABLE c (\n  d INT -- columna\n);\n";

    self::assertSame(["ALTER TABLE a ADD b INT", "CREATE TABLE c (\n  d INT -- columna\n)"], Migrador::sentencias($sql));
  }
}
