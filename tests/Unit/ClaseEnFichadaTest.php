<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\FichajeService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

/**
 * Deducción de la clase de una fichada según los horarios de las clases del alumno.
 */
final class ClaseEnFichadaTest extends TestCase
{
  private const BOXEO = ['id_class' => 1, 'name_class' => 'Boxeo', 'hora_inicio' => '19:00:00', 'hora_fin' => '20:00:00'];
  private const KICK = ['id_class' => 2, 'name_class' => 'Kick Boxing', 'hora_inicio' => '20:00:00', 'hora_fin' => '21:00:00'];

  private function clase(string $hora, array $horarios = [self::BOXEO, self::KICK], array $sinHorario = [], int $total = 2): ?string
  {
    return FichajeService::determinarClase($horarios, $sinHorario, $total, new DateTimeImmutable("2025-05-05 {$hora}"), 30)['name_class'] ?? null;
  }

  public function testDentroDelHorario(): void
  {
    self::assertSame('Boxeo', $this->clase('19:15'));
  }

  public function testHastaMediaHoraAntesCuentaParaEsaClase(): void
  {
    self::assertSame('Boxeo', $this->clase('18:30'));
    self::assertNull($this->clase('18:29'), 'Demasiado temprano');
  }

  public function testEntreDosClasesGanaLaQueEmpiezaMasCerca(): void
  {
    // 19:50: está dentro de Boxeo (19-20) y en la ventana previa de Kick (desde 19:30)
    self::assertSame('Kick Boxing', $this->clase('19:50'));
    self::assertSame('Boxeo', $this->clase('19:20'));
  }

  public function testFueraDeHorarioNoAsignaClase(): void
  {
    self::assertNull($this->clase('22:00'));
    self::assertNull($this->clase('10:00', []));
  }

  public function testUnicaClaseSinHorariosSeAsignaIgual(): void
  {
    $sinHorario = [['id_class' => 3, 'name_class' => 'Muay Thai']];

    self::assertSame('Muay Thai', $this->clase('10:00', [], $sinHorario, 1));
    self::assertNull($this->clase('10:00', [], $sinHorario, 2), 'Con varias clases no se puede adivinar');
  }
}
