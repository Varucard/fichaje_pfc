<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\PagoService;
use DateTimeImmutable;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class PagoServiceTest extends TestCase
{
  public static function fechasDeRenovacion(): array
  {
    return [
      'mes normal' => ['2025-03-15', '2025-04-15'],
      '31 de enero -> fin de febrero' => ['2025-01-31', '2025-02-28'],
      '31 de enero en año bisiesto' => ['2024-01-31', '2024-02-29'],
      '31 de marzo -> 30 de abril' => ['2025-03-31', '2025-04-30'],
      'cambio de año' => ['2025-12-20', '2026-01-20'],
      '30 de enero -> fin de febrero' => ['2025-01-30', '2025-02-28'],
    ];
  }

  #[DataProvider('fechasDeRenovacion')]
  public function testCalculaLaRenovacionUnMesDespues(string $pago, string $esperada): void
  {
    $renovacion = PagoService::calcularRenovacion(new DateTimeImmutable($pago));

    self::assertSame($esperada, $renovacion->format('Y-m-d'));
  }

  public function testSinPagosNoEstaAlDia(): void
  {
    self::assertFalse(PagoService::estaAlDia(null, new DateTimeImmutable('2025-05-01')));
  }

  public function testEstaAlDiaAntesDelVencimiento(): void
  {
    self::assertTrue(PagoService::estaAlDia('2025-05-10', new DateTimeImmutable('2025-05-09 20:00')));
  }

  public function testNoEstaAlDiaDespuesDelVencimiento(): void
  {
    self::assertFalse(PagoService::estaAlDia('2025-05-10', new DateTimeImmutable('2025-05-11 08:00')));
  }

  public function testVenceProximamenteDentroDelMargen(): void
  {
    $hoy = new DateTimeImmutable('2025-05-10');

    self::assertTrue(PagoService::venceProximamente('2025-05-14', $hoy));
    self::assertTrue(PagoService::venceProximamente('2025-05-07', $hoy), 'También marca si venció hace pocos días');
    self::assertFalse(PagoService::venceProximamente('2025-05-20', $hoy));
    self::assertFalse(PagoService::venceProximamente(null, $hoy));
  }
}
