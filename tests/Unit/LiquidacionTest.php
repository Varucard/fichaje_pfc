<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\LiquidacionService;
use PHPUnit\Framework\TestCase;

final class LiquidacionTest extends TestCase
{
  public function testAplicaElPorcentajeSobreLoCobradoEnSusClases(): void
  {
    $clases = [
      ['id_class' => 1, 'name_class' => 'Kick Boxing', 'profesores_en_clase' => 1],
      ['id_class' => 2, 'name_class' => 'Boxeo', 'profesores_en_clase' => 1],
    ];
    $calculo = LiquidacionService::calcular($clases, [1 => 30000.0, 2 => 12000.0, 3 => 99999.0], 50);

    self::assertSame(42000.0, $calculo['base']);
    self::assertSame(21000.0, $calculo['monto']);
    self::assertCount(2, $calculo['detalle']);
  }

  public function testUnaClaseCompartidaSeDivideEntreSusProfesores(): void
  {
    $clases = [['id_class' => 1, 'name_class' => 'Boxeo', 'profesores_en_clase' => 2]];
    $calculo = LiquidacionService::calcular($clases, [1 => 30000.0], 40);

    self::assertSame(15000.0, $calculo['base']);
    self::assertSame(6000.0, $calculo['monto']);
  }

  public function testSinCobrosLiquidaCero(): void
  {
    $clases = [['id_class' => 1, 'name_class' => 'Boxeo', 'profesores_en_clase' => 1]];

    self::assertSame(0.0, LiquidacionService::calcular($clases, [], 50)['monto']);
    self::assertSame(0.0, LiquidacionService::calcular([], [1 => 1000.0], 50)['monto']);
  }

  public function testPeriodoInvalidoUsaElMesActual(): void
  {
    self::assertSame('2025-03', LiquidacionService::validarPeriodo('2025-03'));
    self::assertSame(date('Y-m'), LiquidacionService::validarPeriodo('2025-13'));
    self::assertSame(date('Y-m'), LiquidacionService::validarPeriodo("2025-03' OR 1=1"));
  }
}
