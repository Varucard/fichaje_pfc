<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\PlanDePago;
use App\Exceptions\ValidacionException;
use App\Services\LiquidacionService;
use App\Services\PagoService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class PlanesYAdelantosTest extends TestCase
{
  public function testSumarMesesConservaElDiaOElUltimoDelMes(): void
  {
    $d = fn (string $f) => new DateTimeImmutable($f);

    self::assertSame('2025-04-15', PagoService::sumarMeses($d('2025-03-15'), 1)->format('Y-m-d'));
    self::assertSame('2025-04-30', PagoService::sumarMeses($d('2025-01-31'), 3)->format('Y-m-d'), 'No arrastra el 28 de febrero');
    self::assertSame('2026-01-31', PagoService::sumarMeses($d('2025-01-31'), 12)->format('Y-m-d'));
  }

  public function testAdelantoSeSumaDesdeElVencimientoActual(): void
  {
    // Vence el 20/05 y paga el 15/05: el nuevo vencimiento es 20/06, no 15/06.
    $vence = PagoService::calcularVencimiento(new DateTimeImmutable('2025-05-15'), '2025-05-20', 1);
    self::assertSame('2025-06-20', $vence->format('Y-m-d'));
  }

  public function testPagoDeVariosMesesPorAdelantado(): void
  {
    $vence = PagoService::calcularVencimiento(new DateTimeImmutable('2025-05-15'), '2025-05-20', 3);
    self::assertSame('2025-08-20', $vence->format('Y-m-d'));
  }

  public function testPagoTardioCubreElMesMasViejoAdeudado(): void
  {
    // Venció el 10/03 y paga el 15/06: cubre del 10/03 al 10/04 (sigue debiendo).
    $vence = PagoService::calcularVencimiento(new DateTimeImmutable('2025-06-15'), '2025-03-10', 1);
    self::assertSame('2025-04-10', $vence->format('Y-m-d'));
  }

  public function testPrimerPagoCuentaDesdeLaFechaDePago(): void
  {
    self::assertSame('2025-06-25', PagoService::calcularVencimiento(new DateTimeImmutable('2025-05-25'), null, 1)->format('Y-m-d'));
  }

  public function testReactivadoSeCobraDesdeLaReactivacion(): void
  {
    // Su último vencimiento fue el 10/01, estuvo inactivo y se lo reactivó el 05/06.
    $vence = PagoService::calcularVencimiento(new DateTimeImmutable('2025-06-07'), '2025-01-10', 1, '2025-06-05');
    self::assertSame('2025-07-05', $vence->format('Y-m-d'));
  }

  public function testElDiaAnclaNoArrastraElFinDeMes(): void
  {
    // Pagó el 31/01 (ancla 31): 28/02 y luego 31/03, no 28/03.
    $febrero = PagoService::calcularVencimiento(new DateTimeImmutable('2025-01-31'), null, 1);
    $marzo = PagoService::calcularVencimiento(new DateTimeImmutable('2025-02-20'), $febrero->format('Y-m-d'), 1, null, 31);
    $abril = PagoService::calcularVencimiento(new DateTimeImmutable('2025-03-20'), $marzo->format('Y-m-d'), 1, null, 31);

    self::assertSame(['2025-02-28', '2025-03-31', '2025-04-30'], [$febrero->format('Y-m-d'), $marzo->format('Y-m-d'), $abril->format('Y-m-d')]);
  }

  public function testPrecioYMesesDeUnaPromocion(): void
  {
    $tresMasUno = PlanDePago::deFila(['id' => 1, 'nombre' => '3+1', 'meses_pagos' => 3, 'meses_bonificados' => 1, 'descuento' => 0]);
    self::assertSame(30000.0, $tresMasUno->precio(10000));
    self::assertSame(4, $tresMasUno->mesesCubiertos());

    $semestral = PlanDePago::deFila(['id' => 2, 'nombre' => 'Semestral', 'meses_pagos' => 6, 'meses_bonificados' => 0, 'descuento' => 10]);
    self::assertSame(54000.0, $semestral->precio(10000));
    self::assertSame('Semestral: 6 meses (10 % off)', $semestral->descripcion());
  }

  public function testPlanDeMesesSimples(): void
  {
    self::assertSame(30000.0, PlanDePago::meses(3)->precio(10000));
    $this->expectException(ValidacionException::class);
    PlanDePago::meses(13);
  }

  public function testLiquidacionPorAsistencia(): void
  {
    $clases = [
      ['id_class' => 1, 'name_class' => 'Boxeo', 'profesores_en_clase' => 1],
      ['id_class' => 2, 'name_class' => 'Kick', 'profesores_en_clase' => 2],
    ];
    $calculo = LiquidacionService::calcularAsistencia($clases, [1 => 40, 2 => 30, 3 => 99], 500);

    self::assertSame(55.0, $calculo['base'], '40 + 30/2');
    self::assertSame(27500.0, $calculo['monto']);
  }
}
