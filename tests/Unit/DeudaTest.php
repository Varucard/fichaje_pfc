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

  public function testSinClasesNoHayReparto(): void
  {
    self::assertSame([], PagoService::prorratear(5000, []));
  }

  public function testMesesVencidos(): void
  {
    $vence = '2025-05-10';
    self::assertSame(0, DeudaService::mesesVencidos($vence, new DateTimeImmutable('2025-05-09 12:00')));
    self::assertSame(1, DeudaService::mesesVencidos($vence, new DateTimeImmutable('2025-05-11 08:00')));
    self::assertSame(1, DeudaService::mesesVencidos($vence, new DateTimeImmutable('2025-06-09 23:00')));
    self::assertSame(2, DeudaService::mesesVencidos($vence, new DateTimeImmutable('2025-06-11 08:00')));
    self::assertSame(1, DeudaService::mesesVencidos(null, new DateTimeImmutable('2025-06-11')), 'Nunca pagó: debe un mes');
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
