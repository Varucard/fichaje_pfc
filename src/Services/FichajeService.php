<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Domain\EstadoLectura;
use App\Domain\Llavero;
use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Repositories\FichajeRepository;
use App\Repositories\HorarioRepository;
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
    private readonly HorarioRepository $horarios,
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

    if ($this->fichoRecientemente($idUsuario, $ahora)) {
      throw new ValidacionException('El alumno ya registró su ingreso hace instantes (llavero o fichada manual).');
    }

    $clase = $this->claseDeLaFichada($idUsuario, $ahora);
    $this->fichajes->registrar($idUsuario, $ahora, $clase['id_class'] ?? null);
    $this->auditoria->registrar(
      'fichaje.manual',
      "Fichada manual de {$usuario['user_name']} {$usuario['user_surname']}" . ($clase ? " ({$clase['name_class']})" : ' (sin clase en horario)'),
      'usuario',
      $usuario['dni']
    );
    return $usuario + ['clase_fichada' => $clase['name_class'] ?? null];
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
        // Si se pasa varias veces, queda pendiente (y auditado) una sola vez.
        if ($this->pendientes->registrar($uid)) {
          $this->auditoria->registrar('lector.llavero_desconocido', "Se leyó el llavero desconocido {$uid}", 'llavero', $uid, actor: 'Lector RFID');
        }
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

    $clase = null;
    if ($estado === EstadoLectura::Activo) {
      $clase = $this->claseDeLaFichada($idUsuario, $ahora);
      if (!$this->fichoRecientemente($idUsuario, $ahora)) {
        $this->fichajes->registrar($idUsuario, $ahora, $clase['id_class'] ?? null);
      }
    }

    return $this->respuesta($estado, $usuario, $clase['name_class'] ?? null);
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

  /**
   * Clase a la que corresponde una fichada según el horario (función pura, cubierta por tests).
   *
   * - Ventana válida de cada horario: desde $minutosAntes antes del inicio hasta el fin.
   * - Si coinciden varias, gana la que empieza más cerca de la hora de fichada.
   * - Si ninguna coincide y el alumno tiene una única clase sin horarios cargados, se usa esa.
   * - Si no, null (fichada "sin clase en horario": se registra igual).
   *
   * @param array $horariosDelDia Filas con id_class, name_class, hora_inicio y hora_fin (HH:MM:SS)
   * @param array $clasesSinHorario Clases del alumno sin horarios (id_class, name_class)
   * @return array{id_class: int, name_class: string}|null
   */
  public static function determinarClase(
    array $horariosDelDia,
    array $clasesSinHorario,
    int $totalClasesDelAlumno,
    DateTimeImmutable $ahora,
    int $minutosAntes,
  ): ?array {
    $elegida = null;
    $menorDistancia = PHP_INT_MAX;
    foreach ($horariosDelDia as $horario) {
      $inicio = $ahora->setTime(...array_map('intval', explode(':', $horario['hora_inicio'])));
      $fin = $ahora->setTime(...array_map('intval', explode(':', $horario['hora_fin'])));
      if ($ahora < $inicio->modify("-{$minutosAntes} minutes") || $ahora > $fin) {
        continue;
      }
      $distancia = abs($ahora->getTimestamp() - $inicio->getTimestamp());
      if ($distancia < $menorDistancia) {
        $menorDistancia = $distancia;
        $elegida = ['id_class' => (int) $horario['id_class'], 'name_class' => (string) $horario['name_class']];
      }
    }

    if ($elegida === null && $totalClasesDelAlumno === 1 && count($clasesSinHorario) === 1) {
      $clase = $clasesSinHorario[0];
      $elegida = ['id_class' => (int) $clase['id_class'], 'name_class' => (string) $clase['name_class']];
    }
    return $elegida;
  }

  private function claseDeLaFichada(int $idUsuario, DateTimeImmutable $ahora): ?array
  {
    return self::determinarClase(
      $this->horarios->delAlumnoEnDia($idUsuario, (int) $ahora->format('N')),
      $this->horarios->clasesDelAlumnoSinHorario($idUsuario),
      count($this->matriculas->clasesDeAlumno($idUsuario)),
      $ahora,
      (int) Config::get('fichajes.minutos_antes_de_clase', 30)
    );
  }

  /** Evita fichadas duplicadas si el alumno pasa el llavero varias veces seguidas. */
  private function fichoRecientemente(int $idUsuario, DateTimeImmutable $ahora): bool
  {
    $minutos = (int) Config::get('fichajes.minutos_entre_fichadas', 5);
    return $minutos > 0 && $this->fichajes->fichoDesde($idUsuario, $ahora->modify("-{$minutos} minutes"));
  }

  private function respuesta(EstadoLectura $estado, ?array $usuario = null, ?string $clase = null): array
  {
    return [
      'estado' => $estado->value,
      'nombre' => (string) ($usuario['user_name'] ?? ''),
      'apellido' => (string) ($usuario['user_surname'] ?? ''),
      // Nombre de la clase deducida por horario ('' si no hay). Lo muestra el firmware 3.2+.
      'clase' => (string) ($clase ?? ''),
    ];
  }
}
