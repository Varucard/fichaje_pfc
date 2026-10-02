<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Controllers\Api\ArduinoController;
use App\Domain\Llavero;
use App\Services\UsuarioService;
use PHPUnit\Framework\TestCase;

final class NormalizacionTest extends TestCase
{
  public function testCapitalizaRespetandoTildesYEnie(): void
  {
    self::assertSame('Juan Pérez', UsuarioService::capitalizar('  juan   PÉREZ '));
    self::assertSame('María José Muñoz', UsuarioService::capitalizar('MARÍA JOSÉ muñoz'));
    self::assertSame('', UsuarioService::capitalizar(null));
  }

  public function testLlaveroVacioQuedaSinLlavero(): void
  {
    self::assertSame(Llavero::SIN_LLAVERO, Llavero::normalizar(''));
    self::assertSame(Llavero::SIN_LLAVERO, Llavero::normalizar('   '));
    self::assertSame(Llavero::SIN_LLAVERO, Llavero::normalizar('sin llavero'));
    self::assertFalse(Llavero::asignado(null));
  }

  public function testLlaveroSeNormalizaComoLoEnviaElArduino(): void
  {
    self::assertSame('3A5CF681', Llavero::normalizar(' 3a 5c f6 81 '));
    self::assertTrue(Llavero::asignado('3a5cf681'));
  }

  public function testTextoParaElLcdSinTildesYCon20CaracteresComoMaximo(): void
  {
    self::assertSame('Jose Munoz', ArduinoController::textoLcd('José Muñoz'));
    self::assertSame('Maria Fernanda Gonza', ArduinoController::textoLcd('María Fernanda González'));
  }
}
