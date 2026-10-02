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

  /**
   * Reserva un intento ANTES de comparar la contraseña (el login exitoso lo limpia con
   * exito()). Como el contador es atómico, ni con pedidos simultáneos se comparan más
   * contraseñas que el máximo permitido.
   *
   * @return array{excedido: bool, bloqueado: bool} excedido = ya se habían agotado los
   *   intentos (no comparar la contraseña); bloqueado = con este intento quedó bloqueado.
   */
  public function reservarIntento(?string $dni, string $ip): array
  {
    $ventana = (int) Config::get('login.ventana_minutos', 15);
    $bloqueo = (int) Config::get('login.bloqueo_minutos', 15);
    $claves = ['ip:' . $ip => (int) Config::get('login.max_intentos_ip', 20)];
    if ($dni !== null) {
      $claves['dni:' . $dni] = (int) Config::get('login.max_intentos_dni', 5);
    }

    $resultado = ['excedido' => false, 'bloqueado' => false];
    foreach ($claves as $clave => $maximo) {
      $estado = $this->intentos->registrarFallo($clave, $maximo, $ventana, $bloqueo);
      $fallidos = (int) ($estado['fallidos'] ?? 0);
      $resultado['excedido'] = $resultado['excedido'] || $fallidos > $maximo;
      $resultado['bloqueado'] = $resultado['bloqueado'] || $fallidos >= $maximo;
    }
    return $resultado;
  }

  /**
   * Registra un intento fallido para el DNI (si se conoce) y la IP.
   *
   * @return bool true si con este fallo quedó bloqueado.
   */
  public function fallo(?string $dni, string $ip): bool
  {
    $ventana = (int) Config::get('login.ventana_minutos', 15);
    $bloqueo = (int) Config::get('login.bloqueo_minutos', 15);
    $claves = ['ip:' . $ip => (int) Config::get('login.max_intentos_ip', 20)];
    if ($dni !== null) {
      $claves['dni:' . $dni] = (int) Config::get('login.max_intentos_dni', 5);
    }

    $ahora = new DateTimeImmutable();
    $bloqueado = false;
    foreach ($claves as $clave => $maximo) {
      $estado = $this->intentos->registrarFallo($clave, $maximo, $ventana, $bloqueo);
      $bloqueado = $bloqueado || self::minutosRestantes($estado, $ahora) > 0;
    }
    return $bloqueado;
  }

  public function exito(string $dni, string $ip): void
  {
    $this->intentos->borrar('dni:' . $dni);
    $this->intentos->borrar('ip:' . $ip);
  }
}
