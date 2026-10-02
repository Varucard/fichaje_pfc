<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Domain\EstadoLectura;
use App\Domain\TipoUsuario;
use App\Services\FichajeService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Regla que decide qué muestra el lector Arduino al pasar un llavero.
 */
final class FichajeServiceTest extends TestCase
{
  private DateTimeImmutable $ahora;

  protected function setUp(): void
  {
    $this->ahora = new DateTimeImmutable('2025-05-10 18:00');
  }

  public function testAlumnoConClasesYCuotaAlDiaPuedeIngresar(): void
  {
    $estado = FichajeService::determinarEstado(TipoUsuario::Alumno, '2025-05-20', true, $this->ahora);

    self::assertSame(EstadoLectura::Activo, $estado);
  }

  public function testAlumnoConCuotaVencidaEsMoroso(): void
  {
    $estado = FichajeService::determinarEstado(TipoUsuario::Alumno, '2025-05-01', true, $this->ahora);

    self::assertSame(EstadoLectura::Moroso, $estado);
  }

  public function testAlumnoSinPagosEsMoroso(): void
  {
    $estado = FichajeService::determinarEstado(TipoUsuario::Alumno, null, true, $this->ahora);

    self::assertSame(EstadoLectura::Moroso, $estado);
  }

  public function testAlumnoSinClasesNoPuedeIngresar(): void
  {
    $estado = FichajeService::determinarEstado(TipoUsuario::Alumno, '2025-05-20', false, $this->ahora);

    self::assertSame(EstadoLectura::SinClase, $estado);
  }

  public function testAdministradorEsSaludadoComoAdminAunqueNoTengaClases(): void
  {
    $estado = FichajeService::determinarEstado(TipoUsuario::Administrador, null, false, $this->ahora);

    self::assertSame(EstadoLectura::Admin, $estado);
  }

  public function testProfesorEsSaludadoComoAdmin(): void
  {
    $estado = FichajeService::determinarEstado(TipoUsuario::Profesor, null, false, $this->ahora);

    self::assertSame(EstadoLectura::Admin, $estado);
  }

  public function testLosValoresCoincidenConLosQueEsperaElFirmware(): void
  {
    $firmware = file_get_contents(dirname(__DIR__, 2) . '/firmware/pfc/pfc.ino');

    foreach (EstadoLectura::cases() as $estado) {
      self::assertStringContainsString("\"{$estado->value}\"", $firmware, "El firmware no maneja el estado {$estado->value}");
    }
  }
}
