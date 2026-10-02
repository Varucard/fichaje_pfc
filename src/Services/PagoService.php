<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Domain\PlanDePago;
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
    private readonly EmailService $emails,
  ) {
  }

  /**
   * El vencimiento es un mes después del pago. Si ese día no existe en el mes
   * siguiente (ej: 31/01), se usa el último día del mes (28 o 29/02).
   */
  public static function calcularRenovacion(DateTimeImmutable $fechaPago): DateTimeImmutable
  {
    return self::sumarMeses($fechaPago, 1);
  }

  /**
   * Suma N meses usando un día ancla (por defecto, el día de la fecha); si ese día no
   * existe en el mes destino, usa el último día del mes. Con ancla 31:
   * 31/01 + 1 = 28/02, y 28/02 + 1 = 31/03 (no arrastra el 28).
   */
  public static function sumarMeses(DateTimeImmutable $fecha, int $meses, ?int $diaAncla = null): DateTimeImmutable
  {
    $destino = $fecha->modify('first day of this month')->modify("+{$meses} month");
    $dia = min($diaAncla ?? (int) $fecha->format('d'), (int) $destino->format('t'));
    return $destino->setDate((int) $destino->format('Y'), (int) $destino->format('m'), $dia);
  }

  /**
   * Desde cuándo cubre un pago nuevo (regla del gimnasio):
   * - Si ya tenía un vencimiento (al día o vencido), el pago continúa desde ahí: un
   *   adelanto no pierde días y un pago tardío cubre el mes más viejo adeudado.
   * - Si se lo reactivó después de ese vencimiento, desde la reactivación (no se cobra
   *   el tiempo inactivo).
   * - Si nunca pagó, desde la fecha de pago.
   */
  public static function inicioCobertura(DateTimeImmutable $fechaPago, ?string $vencimientoActual, ?string $cuotaDesde = null): DateTimeImmutable
  {
    if ($cuotaDesde !== null && ($vencimientoActual === null || $cuotaDesde > $vencimientoActual)) {
      return new DateTimeImmutable($cuotaDesde);
    }
    return $vencimientoActual !== null ? new DateTimeImmutable($vencimientoActual) : $fechaPago;
  }

  /** Nuevo vencimiento de un pago que cubre N meses (ver inicioCobertura). */
  public static function calcularVencimiento(
    DateTimeImmutable $fechaPago,
    ?string $vencimientoActual,
    int $meses,
    ?string $cuotaDesde = null,
    ?int $diaAncla = null,
  ): DateTimeImmutable {
    return self::sumarMeses(self::inicioCobertura($fechaPago, $vencimientoActual, $cuotaDesde), $meses, $diaAncla);
  }

  /** La cuota está al día hasta el día del vencimiento inclusive (es lo que dice el comprobante). */
  public static function estaAlDia(?string $renovacion, DateTimeImmutable $ahora): bool
  {
    return $renovacion !== null && $ahora->format('Y-m-d') <= substr($renovacion, 0, 10);
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

    // El ajuste de redondeo va a la clase más cara: así ninguna parte queda negativa.
    $clases = array_values($clases);
    $mayor = 0;
    foreach ($clases as $i => $clase) {
      if ((float) $clase['price_class'] > (float) $clases[$mayor]['price_class']) {
        $mayor = $i;
      }
    }
    $montos = [];
    foreach ($clases as $i => $clase) {
      $montos[$i] = $i === $mayor ? 0.0 : round($monto * (float) $clase['price_class'] / $cuota, 2);
    }
    $montos[$mayor] = round($monto - array_sum($montos), 2);

    $partes = [];
    foreach ($clases as $i => $clase) {
      $parte = $montos[$i];
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
   * Registra un pago. Sin fecha se usa hoy; sin plan, 1 mes; sin monto se cobra el
   * precio del plan (cuota × meses, o el precio de la promoción).
   */
  public function registrar(int $idUsuario, ?DateTimeImmutable $fechaPago = null, ?float $monto = null, ?PlanDePago $plan = null): array
  {
    $usuario = $this->usuarios->buscarPorId($idUsuario)
      ?? throw new ValidacionException('El usuario no existe.');

    if (!(int) $usuario['asset']) {
      throw new ValidacionException('No se pueden registrar pagos de un usuario inactivo.');
    }
    if (TipoUsuario::deUsuario($usuario) !== TipoUsuario::Alumno) {
      throw new ValidacionException('Solo los alumnos pagan cuota.');
    }

    $plan ??= PlanDePago::meses(1);
    $clases = $this->matriculas->clasesDeAlumno($idUsuario);
    $cuota = self::cuota($clases);
    $precio = $plan->precio($cuota);
    $monto = round($monto ?? $precio, 2);
    if ($monto < 0) {
      throw new ValidacionException('El monto no puede ser negativo.');
    }

    $fechaPago ??= new DateTimeImmutable('today');
    if ($fechaPago > new DateTimeImmutable('today')) {
      throw new ValidacionException('La fecha de pago no puede ser futura.');
    }

    $this->pagos->transaccion(function () use ($idUsuario, $usuario, $fechaPago, $monto, $precio, $clases, $plan): void {
      // Bloquea al alumno hasta el fin de la transacción: dos pagos simultáneos no pueden
      // leer el mismo vencimiento, y un doble envío del formulario se rechaza.
      $this->usuarios->bloquearParaActualizar($idUsuario);
      if ($this->pagos->hayPagoReciente($idUsuario, 20)) {
        throw new ValidacionException('Ya se registró un pago para este alumno hace instantes (¿doble clic?). Revisá el historial antes de cargar otro.');
      }

      $ultimo = $this->pagos->ultimoPago($idUsuario);
      $vencimientoActual = $ultimo['renovacion'] ?? null;
      $inicio = self::inicioCobertura($fechaPago, $vencimientoActual, $usuario['cuota_desde'] ?? null);
      // Continúa la cadena de vencimientos: conserva el día ancla del último pago.
      $ancla = $vencimientoActual !== null && $inicio->format('Y-m-d') === $vencimientoActual
        ? (int) ($ultimo['dia_ancla'] ?? $inicio->format('d'))
        : (int) $inicio->format('d');
      $renovacion = self::sumarMeses($inicio, $plan->mesesCubiertos(), $ancla);

      $idPago = $this->pagos->crear($idUsuario, $fechaPago, $renovacion, $monto, $precio, $plan->mesesCubiertos(), $plan->idPromocion, $inicio, $ancla);
      foreach (self::prorratear($monto, $clases) as $parte) {
        $this->pagos->agregarDetalle($idPago, $parte);
      }

      $saldo = $precio - $monto;
      $cobertura = ', cubre desde el ' . $inicio->format('d-m-Y')
        . ($vencimientoActual !== null && $fechaPago->format('Y-m-d') <= $vencimientoActual ? ' (adelanto)' : '')
        . ($vencimientoActual !== null && $fechaPago->format('Y-m-d') > $vencimientoActual && $inicio->format('Y-m-d') === $vencimientoActual ? ' (cuota adeudada)' : '');
      $this->auditoria->registrar(
        'pago.alta',
        sprintf(
          'Pago de %s %s por %s del %s — %s (vence %s)%s%s',
          $usuario['user_name'],
          $usuario['user_surname'],
          dinero($monto),
          $fechaPago->format('d-m-Y'),
          $plan->descripcion(),
          $renovacion->format('d-m-Y'),
          $cobertura,
          $saldo > 0 ? ' — queda un saldo de ' . dinero($saldo) : ''
        ),
        'usuario',
        $usuario['dni'],
        ['id_pago' => $idPago, 'monto' => $monto, 'precio' => $precio, 'meses' => $plan->mesesCubiertos(), 'id_promocion' => $plan->idPromocion],
      );
      $this->avisos->comprobante($idPago, $usuario);
    });

    return $usuario;
  }

  public function registrarPorDni(string $dni, ?DateTimeImmutable $fechaPago = null, ?float $monto = null, ?PlanDePago $plan = null): array
  {
    $usuario = $this->usuarios->buscarPorDni($dni)
      ?? throw new ValidacionException('No existe un usuario con ese DNI.');

    return $this->registrar((int) $usuario['id_user'], $fechaPago, $monto, $plan);
  }

  /** Elimina un pago y devuelve el usuario al que pertenecía. */
  /**
   * Elimina un pago. Solo se puede eliminar el último pago del alumno: los vencimientos
   * se encadenan, así que borrar uno intermedio dejaría vencimientos posteriores que
   * ya no corresponden.
   */
  public function eliminar(int $idPago): array
  {
    $pago = $this->pagos->buscarPorId($idPago)
      ?? throw new ValidacionException('El pago no existe.');

    $usuario = $this->usuarios->buscarPorId((int) $pago['id_user']) ?? [];
    $this->pagos->transaccion(function () use ($pago, $idPago): void {
      $this->usuarios->bloquearParaActualizar((int) $pago['id_user']);
      if ($this->pagos->idUltimoPago((int) $pago['id_user']) !== $idPago) {
        throw new ValidacionException('Solo se puede eliminar el último pago del alumno (los vencimientos se encadenan). Eliminá primero los pagos posteriores.');
      }
      $this->pagos->eliminar($idPago);
      $this->emails->cancelarPorClave('comprobante:' . $idPago);
    });

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
