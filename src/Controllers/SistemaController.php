<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Core\Config;
use App\Services\AuditoriaService;
use App\Services\SistemaService;

final class SistemaController extends Controller
{
  public function __construct(
    private readonly SistemaService $sistema,
    private readonly AuditoriaService $auditoria,
  ) {
  }

  public function reiniciarArduino(Request $request): void
  {
    try {
      $mensaje = $this->sistema->reiniciarArduino();
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/dashboard');
    }

    $this->exito($mensaje, '/dashboard');
  }

  public function backup(Request $request): void
  {
    try {
      $archivo = $this->sistema->generarBackup();
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/sistema/backups');
    }

    $this->exito('Respaldo generado correctamente: ' . basename($archivo), '/sistema/backups');
  }

  public function backups(Request $request): void
  {
    $this->render('sistema/backups', [
      'titulo' => 'Backups de la base de datos',
      'backups' => $this->sistema->listarBackups(),
      'automatico' => (bool) Config::get('backups.automatico', true),
      'hora' => (int) Config::get('backups.hora', 3),
      'retencion' => (int) Config::get('backups.dias_retencion', 30),
    ]);
  }

  public function descargarBackup(Request $request, string $nombre): void
  {
    try {
      $ruta = $this->sistema->rutaBackup($nombre);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/sistema/backups');
    }
    $this->auditoria->registrar('sistema.backup_descarga', "Descarga del respaldo {$nombre}");
    header('Content-Type: application/sql');
    header('Content-Disposition: attachment; filename="' . $nombre . '"');
    header('Content-Length: ' . filesize($ruta));
    readfile($ruta);
  }

  /** Estado del lector para el indicador del dashboard (JSON). */
  public function estadoLector(Request $request): void
  {
    $this->json($this->sistema->estadoLector());
  }
}
