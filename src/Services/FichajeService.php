<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Domain\EstadoLectura;
use App\Domain\Llavero;
use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Repositories\FichajeRepository;
use App\Repositories\LlaveroPendienteRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\PagoRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

/**
 * Fichadas: manuales desde el panel y automáticas desde el lector RFID.
 */
final class FichajeService
{
  public function __construct(
    private readonly FichajeRepository $fichajes,
    private readonly UsuarioRepository $usuarios,
    private readonly PagoRepository $pagos,
    private readonly MatriculaRepository $matriculas,
    private readonly LlaveroPendienteRepository $pendientes,
    private readonly PagoService $pagoService,
    private readonly AuditoriaService $auditoria,
  ) {
  }

  public function ultimos(int $limite = 10): array
  {
    return $this->pagoService->marcarVencimientos($this->fichajes->ultimos($limite));
  }

  public function buscar(string $termino): array
  {
    return $this->pagoService->marcarVencimientos($this->fichajes->buscar($termino));
  }

  /** Fichada manual desde el panel. Devuelve el usuario que fichó. */
  public function registrarManual(string $dni): array
  {
    $usuario = $this->usuarios->buscarPorDni($dni)
      ?? throw new ValidacionException('Usuario inexistente.');

    if (!(int) $usuario['asset']) {
      throw new ValidacionException('El usuario se encuentra inactivo.');
    }
    if (TipoUsuario::deUsuario($usuario) !== TipoUsuario::Alumno) {
      throw new ValidacionException('Solo los alumnos pueden fichar.');
    }

    $idUsuario = (int) $usuario['id_user'];
    $renovacion = $this->pagos->ultimaRenovacion($idUsuario);
    $ahora = new DateTimeImmutable();

    if ($renovacion === null) {
      throw new ValidacionException('El usuario no tiene pagos registrados. Debe abonar antes de fichar.');
    }
    if (!PagoService::estaAlDia($renovacion, $ahora)) {
      throw new ValidacionException('La cuota está vencida. Debe abonar antes de fichar.');
    }
    if (!$this->matriculas->alumnoTieneClases($idUsuario)) {
      throw new ValidacionException('El alumno aún no está registrado en ninguna clase.');
    }

    $this->fichajes->registrar($idUsuario, $ahora);
    $this->auditoria->registrar('fichaje.manual', "Fichada manual de {$usuario['user_name']} {$usuario['user_surname']}", 'usuario', $usuario['dni']);
    return $usuario;
  }

  /**
   * Procesa un llavero leído por el Arduino y devuelve lo que debe mostrar el lector.
   *
   * @return array{estado: string, nombre: string, apellido: string}
   */
  public function procesarLectura(string $uid): array
  {
    $uid = Llavero::normalizar($uid);
    $usuario = $this->usuarios->buscarActivoPorRfid($uid);

    if ($usuario === null) {
      // Si el llavero nunca fue asignado lo dejamos pendiente para que el panel lo muestre.
      if ($this->usuarios->buscarUltimoPorRfid($uid) === null) {
        $this->pendientes->registrar($uid);
        $this->auditoria->registrar('lector.llavero_desconocido', "Se leyó el llavero desconocido {$uid}", 'llavero', $uid, actor: 'Lector RFID');
        return $this->respuesta(EstadoLectura::Desconocido);
      }
      return $this->respuesta(EstadoLectura::Inactivo);
    }

    $idUsuario = (int) $usuario['id_user'];
    $ahora = new DateTimeImmutable();
    $estado = self::determinarEstado(
      TipoUsuario::deUsuario($usuario),
      $this->pagos->ultimaRenovacion($idUsuario),
      $this->matriculas->alumnoTieneClases($idUsuario),
      $ahora
    );

    if ($estado === EstadoLectura::Activo && !$this->fichoRecientemente($idUsuario, $ahora)) {
      $this->fichajes->registrar($idUsuario, $ahora);
    }

    return $this->respuesta($estado, $usuario);
  }

  /**
   * Regla de acceso del lector (función pura, cubierta por tests).
   * Profesores y administradores no fichan: el lector los saluda como "admin".
   * Los alumnos deben estar en al menos una clase y tener la cuota al día.
   */
  public static function determinarEstado(
    TipoUsuario $tipo,
    ?string $renovacion,
    bool $tieneClases,
    DateTimeImmutable $ahora,
  ): EstadoLectura {
    if ($tipo !== TipoUsuario::Alumno) {
      return EstadoLectura::Admin;
    }
    if (!$tieneClases) {
      return EstadoLectura::SinClase;
    }
    return PagoService::estaAlDia($renovacion, $ahora) ? EstadoLectura::Activo : EstadoLectura::Moroso;
  }

  /** Evita fichadas duplicadas si el alumno pasa el llavero varias veces seguidas. */
  private function fichoRecientemente(int $idUsuario, DateTimeImmutable $ahora): bool
  {
    $minutos = (int) Config::get('fichajes.minutos_entre_fichadas', 5);
    return $minutos > 0 && $this->fichajes->fichoDesde($idUsuario, $ahora->modify("-{$minutos} minutes"));
  }

  private function respuesta(EstadoLectura $estado, ?array $usuario = null): array
  {
    return [
      'estado' => $estado->value,
      'nombre' => (string) ($usuario['user_name'] ?? ''),
      'apellido' => (string) ($usuario['user_surname'] ?? ''),
    ];
  }
}
