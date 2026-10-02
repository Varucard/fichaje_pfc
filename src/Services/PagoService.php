<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Repositories\MatriculaRepository;
use App\Repositories\PagoRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

/**
 * Reglas de pagos de cuotas.
 *
 * - La cuota mensual de un alumno es la suma de los precios de sus clases.
 * - Cada pago cubre un mes y guarda el monto cobrado y la cuota que correspondía:
 *   si se cobró menos, la diferencia queda como saldo adeudado (ver DeudaService).
 * - El monto de cada pago se reparte entre las clases del alumno en proporción a su
 *   precio; ese reparto es la base de la liquidación de profesores.
 */
final class PagoService
{
  public function __construct(
    private readonly PagoRepository $pagos,
    private readonly UsuarioRepository $usuarios,
    private readonly MatriculaRepository $matriculas,
    private readonly AuditoriaService $auditoria,
    private readonly AvisosService $avisos,
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

  /** Cuota mensual: suma de los precios de las clases. */
  public static function cuota(array $clases): float
  {
    return round(array_sum(array_map(fn (array $c) => (float) $c['price_class'], $clases)), 2);
  }

  /**
   * Reparte un monto entre las clases en proporción a su precio.
   * El redondeo se ajusta en la última clase para que la suma sea exacta.
   *
   * @return array<int, array{id_class: int, nombre_clase: string, precio_clase: float, monto: float}>
   */
  public static function prorratear(float $monto, array $clases): array
  {
    $cuota = self::cuota($clases);
    if ($cuota <= 0 || !$clases) {
      return [];
    }

    $partes = [];
    $asignado = 0.0;
    $ultima = count($clases) - 1;
    foreach (array_values($clases) as $i => $clase) {
      $parte = $i === $ultima
        ? round($monto - $asignado, 2)
        : round($monto * (float) $clase['price_class'] / $cuota, 2);
      $asignado += $parte;
      $partes[] = [
        'id_class' => (int) $clase['id_class'],
        'nombre_clase' => (string) $clase['name_class'],
        'precio_clase' => (float) $clase['price_class'],
        'monto' => $parte,
      ];
    }
    return $partes;
  }

  /** Cuota mensual actual de un alumno. */
  public function cuotaDe(int $idUsuario): float
  {
    return self::cuota($this->matriculas->clasesDeAlumno($idUsuario));
  }

  /**
   * Registra un pago. Sin fecha se usa hoy; sin monto se cobra la cuota completa.
   */
  public function registrar(int $idUsuario, ?DateTimeImmutable $fechaPago = null, ?float $monto = null): array
  {
    $usuario = $this->usuarios->buscarPorId($idUsuario)
      ?? throw new ValidacionException('El usuario no existe.');

    if (!(int) $usuario['asset']) {
      throw new ValidacionException('No se pueden registrar pagos de un usuario inactivo.');
    }
    if (TipoUsuario::deUsuario($usuario) !== TipoUsuario::Alumno) {
      throw new ValidacionException('Solo los alumnos pagan cuota.');
    }

    $clases = $this->matriculas->clasesDeAlumno($idUsuario);
    $cuota = self::cuota($clases);
    $monto = round($monto ?? $cuota, 2);
    if ($monto < 0) {
      throw new ValidacionException('El monto no puede ser negativo.');
    }

    $fechaPago ??= new DateTimeImmutable('today');
    if ($fechaPago > new DateTimeImmutable('today')) {
      throw new ValidacionException('La fecha de pago no puede ser futura.');
    }
    $renovacion = self::calcularRenovacion($fechaPago);

    $this->pagos->transaccion(function () use ($idUsuario, $usuario, $fechaPago, $renovacion, $monto, $cuota, $clases): void {
      $idPago = $this->pagos->crear($idUsuario, $fechaPago, $renovacion, $monto, $cuota);
      foreach (self::prorratear($monto, $clases) as $parte) {
        $this->pagos->agregarDetalle($idPago, $parte);
      }

      $saldo = $cuota - $monto;
      $this->auditoria->registrar(
        'pago.alta',
        sprintf(
          'Pago de %s %s por %s del %s (vence %s)%s',
          $usuario['user_name'],
          $usuario['user_surname'],
          dinero($monto),
          $fechaPago->format('d-m-Y'),
          $renovacion->format('d-m-Y'),
          $saldo > 0 ? ' — queda un saldo de ' . dinero($saldo) : ''
        ),
        'usuario',
        $usuario['dni'],
        ['id_pago' => $idPago, 'monto' => $monto, 'cuota' => $cuota],
      );
      $this->avisos->comprobante($idPago, $usuario);
    });

    return $usuario;
  }

  public function registrarPorDni(string $dni, ?DateTimeImmutable $fechaPago = null, ?float $monto = null): array
  {
    $usuario = $this->usuarios->buscarPorDni($dni)
      ?? throw new ValidacionException('No existe un usuario con ese DNI.');

    return $this->registrar((int) $usuario['id_user'], $fechaPago, $monto);
  }

  /** Elimina un pago y devuelve el usuario al que pertenecía. */
  public function eliminar(int $idPago): array
  {
    $pago = $this->pagos->buscarPorId($idPago)
      ?? throw new ValidacionException('El pago no existe.');

    $usuario = $this->usuarios->buscarPorId((int) $pago['id_user']) ?? [];
    $this->pagos->eliminar($idPago);

    $this->auditoria->registrar(
      'pago.eliminacion',
      sprintf(
        'Eliminación del pago del %s%s de %s %s',
        fecha($pago['discharge_date']),
        $pago['monto'] !== null ? ' por ' . dinero($pago['monto']) : '',
        $usuario['user_name'] ?? '',
        $usuario['user_surname'] ?? ''
      ),
      'usuario',
      $usuario['dni'] ?? null,
      $pago,
    );

    return $usuario;
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
