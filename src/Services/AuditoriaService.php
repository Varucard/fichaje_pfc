<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Repositories\AuditoriaRepository;
use App\Support\Log;
use Throwable;

/**
 * Historial de acciones: quién hizo qué y cuándo.
 *
 * Las acciones usan el formato "entidad.verbo" (ej: usuario.alta, pago.eliminacion).
 * Se guardan en la misma conexión que la operación, así que si esta se revierte
 * (rollback) el registro de auditoría también se descarta.
 * Un fallo al auditar nunca interrumpe la operación: se registra en el log técnico.
 */
final class AuditoriaService
{
  public const ACCIONES = [
    'sesion' => 'Sesiones',
    'usuario' => 'Usuarios',
    'pago' => 'Pagos',
    'fichaje' => 'Fichajes',
    'clase' => 'Clases',
    'matricula' => 'Matriculaciones',
    'liquidacion' => 'Liquidaciones',
    'stock' => 'Stock',
    'sistema' => 'Sistema',
    'lector' => 'Lector RFID',
  ];

  public function __construct(private readonly AuditoriaRepository $auditoria)
  {
  }

  public function registrar(
    string $accion,
    string $descripcion,
    ?string $entidad = null,
    string|int|null $entidadId = null,
    array $datos = [],
    ?string $actor = null,
  ): void {
    $usuario = Auth::usuario();

    try {
      $this->auditoria->registrar([
        'fecha' => date('Y-m-d H:i:s'),
        'id_usuario' => $usuario['id'] ?? null,
        'usuario' => mb_substr($actor ?? $usuario['nombre'] ?? 'Sistema', 0, 120),
        'accion' => $accion,
        'entidad' => $entidad,
        'entidad_id' => $entidadId === null ? null : (string) $entidadId,
        'descripcion' => mb_substr($descripcion, 0, 500),
        'datos' => $datos ? json_encode($datos, JSON_UNESCAPED_UNICODE) : null,
        'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
      ]);
    } catch (Throwable $e) {
      Log::error("No se pudo registrar la auditoría de {$accion}", $e, ['descripcion' => $descripcion]);
    }
  }

  /** @return array{filas: array, total: int, paginas: int} */
  public function buscar(array $filtros, int $pagina, int $porPagina = 50): array
  {
    $pagina = max(1, $pagina);
    $resultado = $this->auditoria->buscar($filtros, $pagina, $porPagina);
    $resultado['paginas'] = max(1, (int) ceil($resultado['total'] / $porPagina));
    return $resultado;
  }

  public function historialDe(string $entidad, string|int $id): array
  {
    return $this->auditoria->deEntidad($entidad, (string) $id);
  }

  /**
   * Campos que cambiaron entre dos versiones de un registro (para guardar en `datos`).
   *
   * @return array<string, array{antes: mixed, despues: mixed}>
   */
  public static function cambios(array $antes, array $despues, array $campos): array
  {
    $cambios = [];
    foreach ($campos as $campo) {
      $a = $antes[$campo] ?? null;
      $d = $despues[$campo] ?? null;
      if ((string) $a !== (string) $d) {
        $cambios[$campo] = ['antes' => $a, 'despues' => $d];
      }
    }
    return $cambios;
  }
}
