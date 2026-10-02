<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\TipoUsuario;
use App\Repositories\AuditoriaRepository;
use App\Repositories\FichajeRepository;
use App\Repositories\LiquidacionRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\PagoRepository;
use App\Repositories\StockRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

/**
 * Decide qué emails corresponde enviar y los encola.
 *
 * - Programados (bin/procesar-emails.php): vencimiento, deuda, inactividad, cumpleaños y
 *   resumen semanal. Cada uno tiene una clave única, así el proceso se puede ejecutar
 *   muchas veces por día sin repetir mails.
 * - Por evento: bienvenida (alta de cliente) y comprobante (registro de pago).
 *
 * Cada aviso se activa/desactiva y configura desde el panel (Emails).
 */
final class AvisosService
{
  public function __construct(
    private readonly EmailService $emails,
    private readonly ConfiguracionService $config,
    private readonly UsuarioRepository $usuarios,
    private readonly PagoRepository $pagos,
    private readonly MatriculaRepository $matriculas,
    private readonly FichajeRepository $fichajes,
    private readonly StockRepository $stock,
    private readonly LiquidacionRepository $liquidaciones,
    private readonly AuditoriaRepository $auditoria,
    private readonly DeudaService $deudas,
  ) {
  }

  /** Días enteros entre dos fechas (positivo si $hasta es posterior). */
  public static function diasEntre(DateTimeImmutable $desde, DateTimeImmutable $hasta): int
  {
    return (int) $desde->setTime(0, 0)->diff($hasta->setTime(0, 0))->format('%r%a');
  }

  /** ¿Corresponde el aviso de vencimiento? Entre 0 y $anticipacion días antes. */
  public static function correspondeVencimiento(?string $renovacion, DateTimeImmutable $hoy, int $anticipacion): ?int
  {
    if ($renovacion === null) {
      return null;
    }
    $dias = self::diasEntre($hoy, new DateTimeImmutable($renovacion));
    return $dias >= 0 && $dias <= $anticipacion ? $dias : null;
  }

  /** Bloque de N días al que pertenece una fecha: un recordatorio de deuda por bloque. */
  public static function bloqueDeDias(DateTimeImmutable $fecha, int $cadaDias): int
  {
    return intdiv((int) floor($fecha->setTime(0, 0)->getTimestamp() / 86400), max(1, $cadaDias));
  }

  /**
   * Genera y encola los avisos programados del día.
   *
   * @return array<string, int> cantidad encolada por tipo
   */
  public function generar(?DateTimeImmutable $hoy = null): array
  {
    $hoy ??= new DateTimeImmutable('today');
    $encolados = ['vencimiento' => 0, 'deuda' => 0, 'inactividad' => 0, 'cumpleanos' => 0, 'resumen' => 0];

    $cuotas = $this->matriculas->cuotasPorAlumno();
    $resumenPagos = $this->pagos->resumenPorAlumno();
    $ultimasFichadas = $this->fichajes->ultimaPorUsuario();

    foreach ($this->usuarios->alumnosParaAvisos() as $alumno) {
      $id = (int) $alumno['id_user'];
      $cuota = $cuotas[$id] ?? 0.0;
      $renovacion = $resumenPagos[$id]['renovacion'] ?? null;
      $deuda = DeudaService::calcular(
        $cuota,
        $renovacion,
        $resumenPagos[$id]['saldo'] ?? 0.0,
        $hoy,
        $resumenPagos[$id]['dia_ancla'] ?? null,
        $alumno['cuota_desde'] ?? null,
      );

      if ($cuota > 0 && $this->config->activo('aviso.vencimiento.activo')) {
        $dias = self::correspondeVencimiento($renovacion, $hoy, $this->config->entero('aviso.vencimiento.dias'));
        if ($dias !== null && $this->emails->encolarParaUsuario(
          'vencimiento',
          $alumno,
          $dias === 0 ? 'Tu cuota vence hoy' : "Tu cuota vence en {$dias} día" . ($dias > 1 ? 's' : ''),
          ['renovacion' => $renovacion, 'dias' => $dias, 'cuota' => $cuota],
          "vencimiento:{$id}:{$renovacion}"
        )) {
          $encolados['vencimiento']++;
        }
      }

      if ($deuda['total'] > 0 && $this->config->activo('aviso.deuda.activo')) {
        $bloque = self::bloqueDeDias($hoy, $this->config->entero('aviso.deuda.cada_dias'));
        if ($this->emails->encolarParaUsuario(
          'deuda',
          $alumno,
          'Tenés un saldo pendiente de ' . dinero($deuda['total']),
          ['deuda' => $deuda],
          "deuda:{$id}:{$bloque}"
        )) {
          $encolados['deuda']++;
        }
      }

      $ultima = $ultimasFichadas[$id] ?? null;
      if ($ultima !== null && $cuota > 0 && $deuda['total'] <= 0 && $this->config->activo('aviso.inactividad.activo')) {
        $diasSinVenir = self::diasEntre(new DateTimeImmutable($ultima), $hoy);
        if ($diasSinVenir >= $this->config->entero('aviso.inactividad.dias') && $this->emails->encolarParaUsuario(
          'inactividad',
          $alumno,
          '¡Te extrañamos en el gimnasio!',
          ['dias' => $diasSinVenir, 'ultima' => $ultima],
          // Uno por ausencia: si vuelve y deja de venir otra vez, se genera uno nuevo.
          "inactividad:{$id}:" . substr($ultima, 0, 10)
        )) {
          $encolados['inactividad']++;
        }
      }

      if (!empty($alumno['birth_day']) && substr($alumno['birth_day'], 5, 5) === $hoy->format('m-d')
        && $this->config->activo('aviso.cumpleanos.activo')
        && $this->emails->encolarParaUsuario(
          'cumpleanos',
          $alumno,
          '¡Feliz cumpleaños, ' . $alumno['user_name'] . '! 🎉',
          [],
          "cumpleanos:{$id}:" . $hoy->format('Y')
        )) {
        $encolados['cumpleanos']++;
      }
    }

    if ($this->resumenSemanal($hoy)) {
      $encolados['resumen']++;
    }

    return $encolados;
  }

  /** Bienvenida al dar de alta un cliente. */
  public function bienvenida(array $usuario): void
  {
    if (!$this->config->activo('aviso.bienvenida.activo') || TipoUsuario::deUsuario($usuario) !== TipoUsuario::Alumno) {
      return;
    }
    $clases = $this->matriculas->clasesDeAlumno((int) $usuario['id_user']);
    $this->emails->encolarParaUsuario(
      'bienvenida',
      $usuario,
      '¡Bienvenido/a a ' . \App\Core\Config::get('app.nombre') . '!',
      ['clases' => $clases, 'cuota' => PagoService::cuota($clases)],
      'bienvenida:' . $usuario['id_user']
    );
  }

  /** Comprobante en PDF al registrar un pago. */
  public function comprobante(int $idPago, array $usuario): void
  {
    if (!$this->config->activo('aviso.comprobante.activo')) {
      return;
    }
    $pago = $this->pagos->buscarPorId($idPago);
    $this->emails->encolarParaUsuario(
      'comprobante',
      $usuario,
      'Comprobante de pago N° ' . ComprobanteService::numero($idPago),
      ['pago' => $pago, 'numero' => ComprobanteService::numero($idPago)],
      'comprobante:' . $idPago,
      'comprobante:' . $idPago
    );
  }

  /** Resumen semanal para el administrador, el día configurado. */
  private function resumenSemanal(DateTimeImmutable $hoy): bool
  {
    $destinatario = $this->config->get('aviso.resumen.destinatario');
    if (!$this->config->activo('aviso.resumen.activo') || $destinatario === ''
      || (int) $hoy->format('N') !== $this->config->entero('aviso.resumen.dia')) {
      return false;
    }

    $clave = 'resumen:' . $hoy->format('Y-m-d');
    if ($this->emails->yaEncolado($clave)) {
      return false;
    }

    $desde = $hoy->modify('-7 days');
    $hasta = $hoy->modify('-1 day');
    $deudores = $this->deudas->deudores();
    $resumen = [
      'desde' => $desde->format('Y-m-d'),
      'hasta' => $hasta->format('Y-m-d'),
      'fichadas' => $this->fichajes->contarEntre($desde, $hasta),
      'pagos' => $this->pagos->totalEntre($desde, $hasta),
      'altas' => $this->auditoria->contarAccion('usuario.alta', $desde, $hasta),
      'ventas' => $this->stock->ventasEntre($desde, $hasta),
      'deudores' => $deudores['deudores'],
      'deuda_total' => $deudores['total'],
      'stock_bajo' => $this->stock->bajoMinimo(),
      'liquidaciones_pendientes' => $this->liquidaciones->pendientesDePago(),
    ];

    return $this->emails->encolarDirecto(
      'resumen',
      $destinatario,
      'Resumen semanal ' . $desde->format('d/m') . ' al ' . $hasta->format('d/m'),
      ['resumen' => $resumen],
      $clave
    );
  }
}
