<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Services\AdministradorService;
use App\Services\AuthService;
use App\Services\LimiteIntentosService;
use App\Services\ReporteService;
use App\Services\TareasService;
use App\Support\Csv;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class MejorasOperativasTest extends TestCase
{
  public function testMinutosRestantesDeBloqueo(): void
  {
    $ahora = new DateTimeImmutable('2025-05-05 10:00');
    $estado = ['fallidos' => 5, 'primer_fallo' => '2025-05-05 09:55:00', 'bloqueado_hasta' => '2025-05-05 10:15:00'];

    self::assertSame(15, LimiteIntentosService::minutosRestantes($estado, $ahora));
    self::assertSame(0, LimiteIntentosService::minutosRestantes($estado, $ahora->modify('+16 minutes')));
    self::assertSame(0, LimiteIntentosService::minutosRestantes(null, $ahora));
  }

  public function testElDniDelLoginSeNormalizaParaNoSaltearElLimite(): void
  {
    self::assertSame('30111222', AuthService::normalizarDni(' 30.111.222 '));
    self::assertSame('1234567', AuthService::normalizarDni('1234567'));
    self::assertNull(AuthService::normalizarDni('030111222'), '9 dígitos: variante del mismo DNI');
    self::assertNull(AuthService::normalizarDni('30111222a'));
    self::assertNull(AuthService::normalizarDni('"><script>alert(1)</script>'));
  }

  public function testSoloSeRedirigeARutasInternas(): void
  {
    self::assertTrue(es_ruta_interna('/usuarios/30111222'));
    self::assertTrue(es_ruta_interna('/liquidaciones?periodo=2025-05'));
    self::assertFalse(es_ruta_interna('//evil.com'));
    self::assertFalse(es_ruta_interna('/\\evil.com'), 'Los navegadores tratan /\\ como //');
    self::assertFalse(es_ruta_interna('/\\/evil.com'));
    self::assertFalse(es_ruta_interna('https://evil.com'));
    self::assertFalse(es_ruta_interna("/ok\r\nLocation: //evil.com"));
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
}
