<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AdministradorService;
use App\Services\LimiteIntentosService;
use App\Services\ReporteService;
use App\Services\TareasService;
use App\Support\Csv;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class MejorasOperativasTest extends TestCase
{
  public function testBloqueaAlLlegarAlMaximoDeIntentos(): void
  {
    $ahora = new DateTimeImmutable('2025-05-05 10:00');
    $estado = null;
    for ($i = 1; $i <= 4; $i++) {
      $estado = LimiteIntentosService::registrarFallo($estado ? self::fila($estado) : null, $ahora, 5, 15, 15);
      self::assertNull($estado['bloqueado_hasta'], "Intento {$i} todavía no bloquea");
    }
    $estado = LimiteIntentosService::registrarFallo(self::fila($estado), $ahora, 5, 15, 15);
    self::assertSame('2025-05-05 10:15', $estado['bloqueado_hasta']->format('Y-m-d H:i'));
    self::assertSame(15, LimiteIntentosService::minutosRestantes(self::fila($estado), $ahora));
    self::assertSame(0, LimiteIntentosService::minutosRestantes(self::fila($estado), $ahora->modify('+16 minutes')));
  }

  public function testFueraDeLaVentanaSeReiniciaElConteo(): void
  {
    $estado = ['fallidos' => 4, 'primer_fallo' => '2025-05-05 09:00:00', 'bloqueado_hasta' => null];
    $nuevo = LimiteIntentosService::registrarFallo($estado, new DateTimeImmutable('2025-05-05 10:00'), 5, 15, 15);

    self::assertSame(1, $nuevo['fallidos']);
    self::assertNull($nuevo['bloqueado_hasta']);
  }

  public function testBackupAutomaticoUnaVezPorDiaDesdeLaHora(): void
  {
    $temprano = new DateTimeImmutable('2025-05-05 02:59');
    $tarde = new DateTimeImmutable('2025-05-05 03:00');

    self::assertFalse(TareasService::correspondeBackup(true, 3, false, $temprano));
    self::assertTrue(TareasService::correspondeBackup(true, 3, false, $tarde));
    self::assertFalse(TareasService::correspondeBackup(true, 3, true, $tarde), 'Ya hay backup de hoy');
    self::assertFalse(TareasService::correspondeBackup(false, 3, false, $tarde), 'Desactivado');
  }

  public function testReglasDeContrasenia(): void
  {
    self::assertNotNull(AdministradorService::validarPassword('corta', '30111222'));
    self::assertNotNull(AdministradorService::validarPassword('30111222', '30111222'), 'Igual al DNI');
    self::assertNotNull(AdministradorService::validarPassword('11111111', '30111222'), 'Un solo dígito repetido');
    self::assertNull(AdministradorService::validarPassword('Palillo2026!', '30111222'));
  }

  public function testCsvParaExcelEnEspaniol(): void
  {
    self::assertSame('1234,50', Csv::celda(1234.5));
    self::assertSame("'=HYPERLINK(\"x\")", Csv::celda('=HYPERLINK("x")'), 'Evita inyección de fórmulas');
    self::assertSame("'-5", Csv::celda('-5'));
    self::assertSame('', Csv::celda(null));

    $csv = Csv::generar(['Nombre', 'Monto'], [['Muñoz; Ana', 10.0]]);
    self::assertStringStartsWith("\xEF\xBB\xBF", $csv, 'BOM para que Excel respete las tildes');
    self::assertStringContainsString("Nombre;Monto\n\"Muñoz; Ana\";10,00\n", $csv);
  }

  public function testVariacionInteranual(): void
  {
    self::assertSame(25.0, ReporteService::variacion(125000, 100000));
    self::assertSame(-50.0, ReporteService::variacion(50000, 100000));
    self::assertNull(ReporteService::variacion(50000, 0), 'Sin base de comparación');
  }

  private static function fila(array $estado): array
  {
    return [
      'fallidos' => $estado['fallidos'],
      'primer_fallo' => $estado['primer_fallo']->format('Y-m-d H:i:s'),
      'bloqueado_hasta' => $estado['bloqueado_hasta']?->format('Y-m-d H:i:s'),
    ];
  }
}
