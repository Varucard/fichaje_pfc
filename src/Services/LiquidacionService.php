<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Core\Config;
use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Repositories\LiquidacionRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\PagoRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

/**
 * Liquidación mensual de profesores.
 *
 * Para cada clase que dicta el profesor se toma lo cobrado en el mes (según la fecha de
 * pago y el reparto por clase de cada pago). Si la clase tiene varios profesores, ese
 * monto se divide en partes iguales. Al total se le aplica el porcentaje del profesor.
 *
 * Se usan los profesores asignados actualmente a cada clase. Los pagos registrados
 * antes de la v3.1 no tienen reparto por clase y no se incluyen.
 */
final class LiquidacionService
{
  public function __construct(
    private readonly LiquidacionRepository $liquidaciones,
    private readonly PagoRepository $pagos,
    private readonly MatriculaRepository $matriculas,
    private readonly UsuarioRepository $usuarios,
    private readonly AuditoriaService $auditoria,
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

  /**
   * Liquidación de todos los profesores activos para un período (AAAA-MM).
   * Las ya registradas muestran los valores guardados; las demás, el cálculo actual.
   */
  public function resumen(string $periodo): array
  {
    $periodo = self::validarPeriodo($periodo);
    $desde = new DateTimeImmutable($periodo . '-01');
    $cobrado = $this->pagos->cobradoPorClase($desde, $desde->modify('last day of this month'));
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
          'detalle' => json_decode((string) $registrada['detalle'], true) ?: [],
          'base' => (float) $registrada['monto_base'],
          'porcentaje' => (float) $registrada['porcentaje'],
          'monto' => (float) $registrada['monto'],
        ]
        : self::calcular($clasesPorProfesor[$id] ?? [], $cobrado, $this->porcentajeDe($profesor));

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
      ];
    }

    return ['periodo' => $periodo, 'filas' => $filas, 'totales' => $totales];
  }

  public function registrar(int $idProfesor, string $periodo): array
  {
    $periodo = self::validarPeriodo($periodo);
    $profesor = $this->profesor($idProfesor);

    if ($periodo > (new DateTimeImmutable())->format('Y-m')) {
      throw new ValidacionException('No se puede liquidar un período futuro.');
    }
    if ($this->liquidaciones->existe($idProfesor, $periodo)) {
      throw new ValidacionException("La liquidación de {$profesor['user_name']} para {$periodo} ya está registrada.");
    }

    $desde = new DateTimeImmutable($periodo . '-01');
    $clases = array_values(array_filter(
      $this->matriculas->clasesDeProfesores(),
      fn (array $fila) => (int) $fila['id_user'] === $idProfesor
    ));
    $calculo = self::calcular(
      $clases,
      $this->pagos->cobradoPorClase($desde, $desde->modify('last day of this month')),
      $this->porcentajeDe($profesor)
    );

    $this->liquidaciones->transaccion(function () use ($idProfesor, $periodo, $calculo, $profesor): void {
      $id = $this->liquidaciones->crear([
        'id_profesor' => $idProfesor,
        'periodo' => $periodo,
        'monto_base' => $calculo['base'],
        'porcentaje' => $calculo['porcentaje'],
        'monto' => $calculo['monto'],
        'detalle' => json_encode($calculo['detalle'], JSON_UNESCAPED_UNICODE),
        'id_admin' => Auth::usuario()['id'] ?? null,
      ]);
      $this->auditoria->registrar(
        'liquidacion.alta',
        sprintf('Liquidación %s de %s %s: %s (%s%% de %s)', $periodo, $profesor['user_name'], $profesor['user_surname'], dinero($calculo['monto']), $calculo['porcentaje'] + 0, dinero($calculo['base'])),
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

  /** Porcentaje propio del profesor. Vacío = usa el valor por defecto. */
  public function actualizarPorcentaje(int $idProfesor, ?float $porcentaje): void
  {
    $profesor = $this->profesor($idProfesor);
    if ($porcentaje !== null && ($porcentaje < 0 || $porcentaje > 100)) {
      throw new ValidacionException('El porcentaje debe estar entre 0 y 100.');
    }

    $this->usuarios->actualizarPorcentajeLiquidacion($idProfesor, $porcentaje);
    $this->auditoria->registrar(
      'liquidacion.porcentaje',
      sprintf('Porcentaje de liquidación de %s %s: %s', $profesor['user_name'], $profesor['user_surname'], $porcentaje === null ? 'valor por defecto' : ($porcentaje + 0) . '%'),
      'usuario',
      $profesor['dni'],
      ['antes' => $profesor['porcentaje_liquidacion'], 'despues' => $porcentaje],
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
