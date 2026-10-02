<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Exceptions\ValidacionException;
use App\Repositories\PagoRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

/**
 * Reglas de pagos de cuotas. Unifica las tres versiones distintas del cálculo de
 * vencimiento que había repartidas entre controladores.
 */
final class PagoService
{
  public function __construct(
    private readonly PagoRepository $pagos,
    private readonly UsuarioRepository $usuarios,
  ) {
  }

  /**
   * El vencimiento es un mes después del pago. Si ese día no existe en el mes
   * siguiente (ej: 31/01), se usa el último día del mes (28 o 29/02).
   */
  public static function calcularRenovacion(DateTimeImmutable $fechaPago): DateTimeImmutable
  {
    $renovacion = $fechaPago->modify('+1 month');
    if ((int) $renovacion->format('d') < (int) $fechaPago->format('d')) {
      $renovacion = $renovacion->modify('last day of previous month');
    }
    return $renovacion;
  }

  /** La cuota está al día si la fecha actual no superó el vencimiento. */
  public static function estaAlDia(?string $renovacion, DateTimeImmutable $ahora): bool
  {
    return $renovacion !== null && $ahora <= new DateTimeImmutable($renovacion);
  }

  /** True si faltan (o pasaron) pocos días del vencimiento, para resaltarlo en rojo. */
  public static function venceProximamente(?string $renovacion, DateTimeImmutable $ahora, int $dias = 5): bool
  {
    if ($renovacion === null) {
      return false;
    }
    return (new DateTimeImmutable($renovacion))->diff($ahora)->days <= $dias;
  }

  /** Registra un pago (hoy o en la fecha indicada) para un usuario. */
  public function registrar(int $idUsuario, ?DateTimeImmutable $fechaPago = null): array
  {
    $usuario = $this->usuarios->buscarPorId($idUsuario)
      ?? throw new ValidacionException('El usuario no existe.');

    if (!(int) $usuario['asset']) {
      throw new ValidacionException('No se pueden registrar pagos de un usuario inactivo.');
    }

    $fechaPago ??= new DateTimeImmutable('today');
    $this->pagos->crear($idUsuario, $fechaPago, self::calcularRenovacion($fechaPago));

    return $usuario;
  }

  public function registrarPorDni(string $dni, ?DateTimeImmutable $fechaPago = null): array
  {
    $usuario = $this->usuarios->buscarPorDni($dni)
      ?? throw new ValidacionException('No existe un usuario con ese DNI.');

    return $this->registrar((int) $usuario['id_user'], $fechaPago);
  }

  /** Elimina un pago y devuelve el usuario al que pertenecía. */
  public function eliminar(int $idPago): array
  {
    $pago = $this->pagos->buscarPorId($idPago)
      ?? throw new ValidacionException('El pago no existe.');

    $this->pagos->eliminar($idPago);

    return $this->usuarios->buscarPorId((int) $pago['id_user']) ?? [];
  }

  public function ultimosDeUsuario(int $idUsuario): array
  {
    return $this->pagos->ultimosDeUsuario($idUsuario);
  }

  /** Agrega a cada fila la marca 'pago_cerca' según su fecha de vencimiento. */
  public function marcarVencimientos(array $filas): array
  {
    $ahora = new DateTimeImmutable();
    $dias = (int) Config::get('pagos.dias_aviso_vencimiento', 5);

    return array_map(function (array $fila) use ($ahora, $dias): array {
      $fila['pago_cerca'] = self::venceProximamente($fila['date_of_renovation'] ?? null, $ahora, $dias);
      return $fila;
    }, $filas);
  }
}
