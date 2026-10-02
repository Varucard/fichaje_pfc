<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Config;
use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Repositories\FichajeRepository;
use App\Repositories\LiquidacionRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\PagoRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

/**
 * Liquidación mensual de profesores. Cada profesor se liquida en uno de dos modos:
 *
 * - porcentaje: % de lo cobrado en el mes en sus clases (según el reparto por clase de
 *   cada pago; un pago de varios meses se reparte entre esos meses).
 * - asistencia: $ fijo por cada ingreso de un alumno a sus clases en el mes (según la
 *   clase deducida por horario en cada fichada).
 *
 * Si una clase tiene varios profesores, la base de esa clase se divide en partes iguales.
 * Se usan los profesores asignados actualmente a cada clase.
 */
final class LiquidacionService
{
  public function __construct(
    private readonly LiquidacionRepository $liquidaciones,
    private readonly PagoRepository $pagos,
    private readonly MatriculaRepository $matriculas,
    private readonly UsuarioRepository $usuarios,
    private readonly AuditoriaService $auditoria,
    private readonly FichajeRepository $fichajes,
  ) {
  }

  /**
   * Cálculo puro de la liquidación de un profesor.
   *
   * @param array $clases Filas con id_class, name_class y profesores_en_clase
   * @param array<int, float> $cobradoPorClase id_class => total cobrado en el período
   * @return array{detalle: array, base: float, porcentaje: float, monto: float}
   */
  public static function calcular(array $clases, array $cobradoPorClase, float $porcentaje): array
  {
    $detalle = [];
    $base = 0.0;
    foreach ($clases as $clase) {
      $cobrado = (float) ($cobradoPorClase[(int) $clase['id_class']] ?? 0);
      $profesores = max(1, (int) $clase['profesores_en_clase']);
      $parte = round($cobrado / $profesores, 2);
      $base += $parte;
      $detalle[] = [
        'id_class' => (int) $clase['id_class'],
        'clase' => $clase['name_class'],
        'cobrado' => $cobrado,
        'profesores' => $profesores,
        'base' => $parte,
      ];
    }

    return [
      'detalle' => $detalle,
      'base' => round($base, 2),
      'porcentaje' => $porcentaje,
      'monto' => round($base * $porcentaje / 100, 2),
    ];
  }

  /**
   * Cálculo puro del modo asistencia.
   *
   * @param array<int, int> $asistenciasPorClase id_class => ingresos en el período
   * @return array{detalle: array, base: float, monto_por_asistencia: float, monto: float}
   */
  public static function calcularAsistencia(array $clases, array $asistenciasPorClase, float $montoPorAsistencia): array
  {
    $detalle = [];
    $base = 0.0;
    foreach ($clases as $clase) {
      $asistencias = (int) ($asistenciasPorClase[(int) $clase['id_class']] ?? 0);
      $profesores = max(1, (int) $clase['profesores_en_clase']);
      // Sin redondear: con 3 profesores, 1 asistencia vale 1/3 para cada uno.
      $parte = $asistencias / $profesores;
      $base += $parte;
      $detalle[] = [
        'id_class' => (int) $clase['id_class'],
        'clase' => $clase['name_class'],
        'asistencias' => $asistencias,
        'profesores' => $profesores,
        'base' => $parte,
      ];
    }

    return [
      'detalle' => $detalle,
      'base' => round($base, 4),
      'monto_por_asistencia' => $montoPorAsistencia,
      'monto' => round($base * $montoPorAsistencia, 2),
    ];
  }

  public static function validarPeriodo(?string $periodo): string
  {
    $periodo = (string) $periodo;
    if (!preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $periodo)) {
      return (new DateTimeImmutable())->format('Y-m');
    }
    return $periodo;
  }

  public function porcentajeDe(array $profesor): float
  {
    return $profesor['porcentaje_liquidacion'] !== null
      ? (float) $profesor['porcentaje_liquidacion']
      : (float) Config::get('liquidaciones.porcentaje_defecto', 50);
  }

  public static function modoDe(array $profesor): string
  {
    return ($profesor['modo_liquidacion'] ?? null) === 'asistencia' ? 'asistencia' : 'porcentaje';
  }

  /** Calcula la liquidación de un profesor según su modo. Devuelve el cálculo con 'modo'. */
  private function calcularPara(array $profesor, array $clases, array $cobrado, array $asistencias): array
  {
    if (self::modoDe($profesor) === 'asistencia') {
      return ['modo' => 'asistencia', 'porcentaje' => null]
        + self::calcularAsistencia($clases, $asistencias, (float) ($profesor['monto_por_asistencia'] ?? 0));
    }
    return ['modo' => 'porcentaje', 'monto_por_asistencia' => null]
      + self::calcular($clases, $cobrado, $this->porcentajeDe($profesor));
  }

  /** @return array{0: array<int, float>, 1: array<int, int>} cobrado y asistencias por clase del período */
  private function basesDelPeriodo(string $periodo): array
  {
    $desde = new DateTimeImmutable($periodo . '-01');
    return [
      $this->pagos->cobradoPorClase($periodo),
      $this->fichajes->asistenciasPorClase($desde, $desde->modify('last day of this month')),
    ];
  }

  /**
   * Liquidación de todos los profesores activos para un período (AAAA-MM).
   * Las ya registradas muestran los valores guardados; las demás, el cálculo actual.
   */
  public function resumen(string $periodo): array
  {
    $periodo = self::validarPeriodo($periodo);
    [$cobrado, $asistencias] = $this->basesDelPeriodo($periodo);
    $registradas = $this->liquidaciones->delPeriodo($periodo);

    $clasesPorProfesor = [];
    foreach ($this->matriculas->clasesDeProfesores() as $fila) {
      $clasesPorProfesor[(int) $fila['id_user']][] = $fila;
    }

    $filas = [];
    $totales = ['a_liquidar' => 0.0, 'registrado' => 0.0, 'pagado' => 0.0];
    foreach ($this->usuarios->listarPorTipo(TipoUsuario::Profesor) as $profesor) {
      $id = (int) $profesor['id_user'];
      $registrada = $registradas[$id] ?? null;
      if (!$registrada && !(int) $profesor['asset']) {
        continue;
      }

      $calculo = $registrada
        ? [
          'modo' => $registrada['modo'],
          'detalle' => json_decode((string) $registrada['detalle'], true) ?: [],
          'base' => (float) $registrada['monto_base'],
          'porcentaje' => $registrada['porcentaje'] !== null ? (float) $registrada['porcentaje'] : null,
          'monto_por_asistencia' => $registrada['monto_por_asistencia'] !== null ? (float) $registrada['monto_por_asistencia'] : null,
          'monto' => (float) $registrada['monto'],
        ]
        : $this->calcularPara($profesor, $clasesPorProfesor[$id] ?? [], $cobrado, $asistencias);

      $totales['a_liquidar'] += $calculo['monto'];
      if ($registrada) {
        $totales['registrado'] += $calculo['monto'];
        if ($registrada['pagada']) {
          $totales['pagado'] += $calculo['monto'];
        }
      }

      $filas[] = [
        'profesor' => $profesor,
        'calculo' => $calculo,
        'registrada' => $registrada,
        'porcentaje_propio' => $profesor['porcentaje_liquidacion'] !== null,
        'porcentaje_defecto' => (float) Config::get('liquidaciones.porcentaje_defecto', 50),
      ];
    }

    return ['periodo' => $periodo, 'filas' => $filas, 'totales' => $totales];
  }

  public function registrar(int $idProfesor, string $periodo): array
  {
    $periodo = self::validarPeriodo($periodo);
    $profesor = $this->profesor($idProfesor);

    if ($periodo >= (new DateTimeImmutable())->format('Y-m')) {
      throw new ValidacionException('Solo se pueden registrar liquidaciones de meses cerrados: el cálculo del mes en curso todavía puede cambiar.');
    }
    if ($this->liquidaciones->existe($idProfesor, $periodo)) {
      throw new ValidacionException("La liquidación de {$profesor['user_name']} para {$periodo} ya está registrada.");
    }

    $clases = array_values(array_filter(
      $this->matriculas->clasesDeProfesores(),
      fn (array $fila) => (int) $fila['id_user'] === $idProfesor
    ));
    [$cobrado, $asistencias] = $this->basesDelPeriodo($periodo);
    $calculo = $this->calcularPara($profesor, $clases, $cobrado, $asistencias);

    $this->liquidaciones->transaccion(function () use ($idProfesor, $periodo, $calculo, $profesor): void {
      $id = $this->liquidaciones->crear([
        'id_profesor' => $idProfesor,
        'periodo' => $periodo,
        'monto_base' => $calculo['base'],
        'porcentaje' => $calculo['porcentaje'],
        'modo' => $calculo['modo'],
        'monto_por_asistencia' => $calculo['monto_por_asistencia'],
        'monto' => $calculo['monto'],
        'detalle' => json_encode($calculo['detalle'], JSON_UNESCAPED_UNICODE),
        'id_admin' => Auth::usuario()['id'] ?? null,
      ]);
      $this->auditoria->registrar(
        'liquidacion.alta',
        sprintf(
          'Liquidación %s de %s %s: %s (%s)',
          $periodo,
          $profesor['user_name'],
          $profesor['user_surname'],
          dinero($calculo['monto']),
          $calculo['modo'] === 'asistencia'
            ? ($calculo['base'] + 0) . ' asistencias × ' . dinero($calculo['monto_por_asistencia'])
            : ($calculo['porcentaje'] + 0) . '% de ' . dinero($calculo['base'])
        ),
        'usuario',
        $profesor['dni'],
        ['id_liquidacion' => $id] + $calculo,
      );
    });

    return $calculo;
  }

  public function marcarPagada(int $id): array
  {
    $liquidacion = $this->liquidaciones->buscarPorId($id) ?? throw new ValidacionException('La liquidación no existe.');
    if ($liquidacion['pagada']) {
      throw new ValidacionException('La liquidación ya estaba pagada.');
    }

    $this->liquidaciones->marcarPagada($id);
    $this->auditoria->registrar(
      'liquidacion.pago',
      "Pago de la liquidación {$liquidacion['periodo']} de {$liquidacion['user_name']} {$liquidacion['user_surname']} por " . dinero($liquidacion['monto']),
      'usuario',
      $liquidacion['dni'],
    );
    return $liquidacion;
  }

  /** Anula una liquidación no pagada (por ejemplo, para recalcularla). */
  public function anular(int $id): array
  {
    $liquidacion = $this->liquidaciones->buscarPorId($id) ?? throw new ValidacionException('La liquidación no existe.');
    if ($liquidacion['pagada']) {
      throw new ValidacionException('No se puede anular una liquidación ya pagada.');
    }

    $this->liquidaciones->eliminar($id);
    $this->auditoria->registrar(
      'liquidacion.anulacion',
      "Anulación de la liquidación {$liquidacion['periodo']} de {$liquidacion['user_name']} {$liquidacion['user_surname']} (" . dinero($liquidacion['monto']) . ')',
      'usuario',
      $liquidacion['dni'],
      $liquidacion,
    );
    return $liquidacion;
  }

  /**
   * Modo de liquidación del profesor y su valor:
   * - porcentaje: % (vacío = porcentaje por defecto)
   * - asistencia: $ por asistencia (obligatorio)
   */
  public function actualizarConfiguracion(int $idProfesor, string $modo, ?float $valor): void
  {
    $profesor = $this->profesor($idProfesor);

    if ($modo === 'asistencia') {
      if ($valor === null || $valor <= 0) {
        throw new ValidacionException('Ingresá el monto por asistencia.');
      }
      $this->usuarios->actualizarLiquidacion($idProfesor, 'asistencia', $profesor['porcentaje_liquidacion'] !== null ? (float) $profesor['porcentaje_liquidacion'] : null, $valor);
      $detalle = dinero($valor) . ' por asistencia';
    } elseif ($modo === 'porcentaje') {
      if ($valor !== null && ($valor < 0 || $valor > 100)) {
        throw new ValidacionException('El porcentaje debe estar entre 0 y 100.');
      }
      $this->usuarios->actualizarLiquidacion($idProfesor, null, $valor, $profesor['monto_por_asistencia'] !== null ? (float) $profesor['monto_por_asistencia'] : null);
      $detalle = $valor === null ? 'porcentaje por defecto' : ($valor + 0) . '% de lo cobrado';
    } else {
      throw new ValidacionException('Modo de liquidación no válido.');
    }

    $this->auditoria->registrar(
      'liquidacion.configuracion',
      "Liquidación de {$profesor['user_name']} {$profesor['user_surname']}: {$detalle}",
      'usuario',
      $profesor['dni'],
      ['modo' => $modo, 'valor' => $valor],
    );
  }

  public function historialDe(int $idProfesor): array
  {
    return $this->liquidaciones->deProfesor($idProfesor);
  }

  private function profesor(int $id): array
  {
    $profesor = $this->usuarios->buscarPorId($id);
    if (!$profesor || TipoUsuario::deUsuario($profesor) !== TipoUsuario::Profesor) {
      throw new ValidacionException('El profesor no existe.');
    }
    return $profesor;
  }
}
