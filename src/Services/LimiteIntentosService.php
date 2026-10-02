<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Repositories\IntentoLoginRepository;
use DateTimeImmutable;

/**
 * Bloqueo temporal ante intentos fallidos de inicio de sesión (fuerza bruta).
 * Se cuenta por DNI y por IP: si se supera el máximo dentro de la ventana, la clave
 * queda bloqueada durante los minutos de bloqueo.
 */
final class LimiteIntentosService
{
  public function __construct(private readonly IntentoLoginRepository $intentos)
  {
  }

  /**
   * Regla pura (cubierta por tests): nuevo estado después de un intento fallido.
   *
   * @return array{fallidos: int, primer_fallo: DateTimeImmutable, bloqueado_hasta: ?DateTimeImmutable}
   */
  public static function registrarFallo(?array $estado, DateTimeImmutable $ahora, int $maximo, int $ventanaMin, int $bloqueoMin): array
  {
    $primer = $estado ? new DateTimeImmutable($estado['primer_fallo']) : $ahora;
    $fallidos = $estado ? (int) $estado['fallidos'] : 0;

    // Fuera de la ventana se empieza a contar de nuevo.
    if ($primer < $ahora->modify("-{$ventanaMin} minutes")) {
      $primer = $ahora;
      $fallidos = 0;
    }
    $fallidos++;

    return [
      'fallidos' => $fallidos,
      'primer_fallo' => $primer,
      'bloqueado_hasta' => $fallidos >= $maximo ? $ahora->modify("+{$bloqueoMin} minutes") : null,
    ];
  }

  /** Minutos que faltan para que se desbloquee (0 = no está bloqueada). */
  public static function minutosRestantes(?array $estado, DateTimeImmutable $ahora): int
  {
    if (!$estado || empty($estado['bloqueado_hasta'])) {
      return 0;
    }
    $hasta = new DateTimeImmutable($estado['bloqueado_hasta']);
    return $hasta > $ahora ? (int) ceil(($hasta->getTimestamp() - $ahora->getTimestamp()) / 60) : 0;
  }

  /** @return int Minutos de bloqueo restantes para el DNI o la IP (el mayor). */
  public function bloqueo(string $dni, string $ip): int
  {
    $ahora = new DateTimeImmutable();
    return max(
      self::minutosRestantes($this->intentos->obtener('dni:' . $dni), $ahora),
      self::minutosRestantes($this->intentos->obtener('ip:' . $ip), $ahora),
    );
  }

  /** @return bool true si con este fallo quedó bloqueado. */
  public function fallo(string $dni, string $ip): bool
  {
    $ahora = new DateTimeImmutable();
    $ventana = (int) Config::get('login.ventana_minutos', 15);
    $bloqueo = (int) Config::get('login.bloqueo_minutos', 15);
    $bloqueado = false;

    foreach (['dni:' . $dni => (int) Config::get('login.max_intentos_dni', 5), 'ip:' . $ip => (int) Config::get('login.max_intentos_ip', 20)] as $clave => $maximo) {
      $estado = self::registrarFallo($this->intentos->obtener($clave), $ahora, $maximo, $ventana, $bloqueo);
      $this->intentos->guardar(
        $clave,
        $estado['fallidos'],
        $estado['primer_fallo']->format('Y-m-d H:i:s'),
        $estado['bloqueado_hasta']?->format('Y-m-d H:i:s')
      );
      $bloqueado = $bloqueado || $estado['bloqueado_hasta'] !== null;
    }
    return $bloqueado;
  }

  public function exito(string $dni, string $ip): void
  {
    $this->intentos->borrar('dni:' . $dni);
    $this->intentos->borrar('ip:' . $ip);
  }
}
