<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AvisosService;
use App\Services\ComprobanteService;
use App\Services\EmailService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class AvisosTest extends TestCase
{
  public function testAvisoDeVencimientoDentroDeLaAnticipacion(): void
  {
    $hoy = new DateTimeImmutable('2025-05-07');

    self::assertSame(3, AvisosService::correspondeVencimiento('2025-05-10', $hoy, 3));
    self::assertSame(0, AvisosService::correspondeVencimiento('2025-05-07', $hoy, 3), 'Vence hoy');
    self::assertNull(AvisosService::correspondeVencimiento('2025-05-11', $hoy, 3), 'Todavía falta');
    self::assertNull(AvisosService::correspondeVencimiento('2025-05-06', $hoy, 3), 'Ya venció: corresponde el aviso de deuda');
    self::assertNull(AvisosService::correspondeVencimiento(null, $hoy, 3));
  }

  public function testRecordatorioDeDeudaUnoPorBloqueDeDias(): void
  {
    $lunes = new DateTimeImmutable('2025-05-05');
    $bloque = AvisosService::bloqueDeDias($lunes, 7);

    // Cada bloque (= un recordatorio como máximo) abarca 7 días consecutivos, nunca más.
    $diasPorBloque = [];
    $anterior = null;
    $cambios = 0;
    for ($i = 0; $i < 70; $i++) {
      $actual = AvisosService::bloqueDeDias($lunes->modify("+{$i} days"), 7);
      $diasPorBloque[$actual] = ($diasPorBloque[$actual] ?? 0) + 1;
      $cambios += $anterior !== null && $actual !== $anterior ? 1 : 0;
      $anterior = $actual;
    }
    self::assertLessThanOrEqual(7, max($diasPorBloque));
    self::assertSame(count($diasPorBloque) - 1, $cambios, 'Los días de un bloque son consecutivos');
    self::assertContains(count($diasPorBloque), [10, 11], '70 días = 10 u 11 bloques según dónde arranque');
    self::assertNotSame($bloque, AvisosService::bloqueDeDias($lunes->modify('+7 days'), 7), 'A los 7 días cambia el bloque');
  }

  public function testDiasEntreIgnoraLaHora(): void
  {
    self::assertSame(1, AvisosService::diasEntre(new DateTimeImmutable('2025-05-01 23:59'), new DateTimeImmutable('2025-05-02 00:01')));
    self::assertSame(-2, AvisosService::diasEntre(new DateTimeImmutable('2025-05-03'), new DateTimeImmutable('2025-05-01')));
  }

  public function testVersionEnTextoDelEmail(): void
  {
    $html = '<html><head><style>p{color:red}</style></head><body><p>Hola <strong>Ana</strong></p><p>Ver <a href="https://x.com/baja">acá</a></p></body></html>';

    self::assertSame("Hola Ana\nVer acá (https://x.com/baja)", EmailService::aTexto($html));
  }

  public function testNumeroDeComprobante(): void
  {
    self::assertSame('00000123', ComprobanteService::numero(123));
  }

  public function testCumpleaniosDel29DeFebrero(): void
  {
    self::assertTrue(AvisosService::cumpleHoy('2000-02-29', new DateTimeImmutable('2025-02-28')), 'Año no bisiesto: se saluda el 28/02');
    self::assertFalse(AvisosService::cumpleHoy('2000-02-29', new DateTimeImmutable('2024-02-28')), 'Año bisiesto: se espera al 29');
    self::assertTrue(AvisosService::cumpleHoy('2000-02-29', new DateTimeImmutable('2024-02-29')));
    self::assertTrue(AvisosService::cumpleHoy('1990-05-10', new DateTimeImmutable('2025-05-10')));
  }

  public function testMontosConCentavosSoloCuandoLosHay(): void
  {
    self::assertSame('$12.500', dinero(12500));
    self::assertSame('$0,40', dinero(0.4), 'Un saldo de 40 centavos no se muestra como $0');
    self::assertSame('$3.333,33', dinero(3333.333));
    self::assertSame('-$150', dinero(-150));
  }
}
