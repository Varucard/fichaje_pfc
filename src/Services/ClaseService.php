<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Repositories\ClaseRepository;
use App\Repositories\FichajeRepository;
use App\Repositories\HorarioRepository;
use App\Repositories\MatriculaRepository;
use App\Repositories\UsuarioRepository;

/**
 * Clases y sus matriculaciones (alumnos y profesores).
 */
final class ClaseService
{
  public function __construct(
    private readonly ClaseRepository $clases,
    private readonly MatriculaRepository $matriculas,
    private readonly UsuarioRepository $usuarios,
    private readonly AuditoriaService $auditoria,
    private readonly HorarioRepository $horarios,
    private readonly FichajeRepository $fichajes,
  ) {
  }

  public function listar(): array
  {
    return $this->clases->listar();
  }

  public function buscar(string $termino): array
  {
    return trim($termino) === '' ? $this->clases->listar() : $this->clases->buscarPorNombre(trim($termino));
  }

  public function detalle(int $id): array
  {
    $clase = $this->clases->buscarPorId($id)
      ?? throw new ValidacionException('No se encontró la clase.');

    return [
      'clase' => $clase,
      'profesores' => $this->matriculas->profesoresDeClase($id),
      'alumnos' => $this->matriculas->alumnosDeClase($id),
      'horarios' => $this->horarios->deClase($id),
      'asistencias' => $this->fichajes->asistenciasAClase($id),
    ];
  }

  /**
   * Crea una clase y le asigna los profesores elegidos.
   *
   * @param array<int, string|int> $idsProfesores
   * @return array{id: int, omitidos: array<int, string>}
   */
  public function crear(string $nombre, string $precio, array $idsProfesores, array $idsAlumnos = []): array
  {
    [$nombre, $precio] = $this->validar($nombre, $precio);

    if ($this->clases->buscarPorNombreExacto($nombre)) {
      throw new ValidacionException("La clase \"{$nombre}\" ya existe.");
    }

    return $this->clases->transaccion(function () use ($nombre, $precio, $idsProfesores, $idsAlumnos): array {
      $id = $this->clases->crear($nombre, $precio);
      $omitidos = [];
      $asignados = [];

      foreach (array_unique(array_map('intval', $idsProfesores)) as $idProfesor) {
        $profesor = $this->usuarios->buscarPorId($idProfesor);
        if (!$profesor || TipoUsuario::deUsuario($profesor) !== TipoUsuario::Profesor || !(int) $profesor['asset']) {
          $omitidos[] = $profesor['user_name'] ?? "ID {$idProfesor}";
          continue;
        }
        $this->matriculas->asignarProfesor($idProfesor, $id);
        $asignados[] = trim($profesor['user_name'] . ' ' . $profesor['user_surname']);
      }

      $alumnos = [];
      foreach (array_unique(array_map('intval', $idsAlumnos)) as $idAlumno) {
        $alumno = $this->usuarios->buscarPorId($idAlumno);
        if (!$alumno || TipoUsuario::deUsuario($alumno) !== TipoUsuario::Alumno || !(int) $alumno['asset']) {
          $omitidos[] = $alumno['user_name'] ?? "ID {$idAlumno}";
          continue;
        }
        $this->matriculas->inscribirAlumno($idAlumno, $id);
        $alumnos[] = trim($alumno['user_name'] . ' ' . $alumno['user_surname']);
      }

      $this->auditoria->registrar('clase.alta', "Alta de la clase {$nombre} (" . dinero($precio) . ')'
        . ($alumnos ? ' con ' . count($alumnos) . ' alumno(s)' : ''), 'clase', $id, [
        'profesores' => $asignados,
        'alumnos' => $alumnos,
      ]);
      return ['id' => $id, 'omitidos' => $omitidos];
    });
  }

  public function actualizar(int $id, string $nombre, string $precio): void
  {
    $anterior = $this->clases->buscarPorId($id) ?? throw new ValidacionException('No se encontró la clase.');
    [$nombre, $precio] = $this->validar($nombre, $precio);

    $otra = $this->clases->buscarPorNombreExacto($nombre);
    if ($otra && (int) $otra['id_class'] !== $id) {
      throw new ValidacionException("Ya existe otra clase llamada \"{$nombre}\".");
    }

    $this->clases->actualizar($id, $nombre, $precio);

    $cambios = AuditoriaService::cambios($anterior, ['name_class' => $nombre, 'price_class' => $precio], ['name_class', 'price_class']);
    if ($cambios) {
      $this->auditoria->registrar('clase.actualizacion', "Actualización de la clase {$nombre}", 'clase', $id, $cambios);
    }
  }

  /**
   * Elimina una clase. Si tiene alumnos o profesores, solo se elimina cuando
   * $incluirMatriculaciones es true (el usuario lo confirmó).
   */
  public function eliminar(int $id, bool $incluirMatriculaciones): void
  {
    $clase = $this->clases->buscarPorId($id) ?? throw new ValidacionException('No se encontró la clase.');

    if (!$incluirMatriculaciones && $this->matriculas->claseTieneMatriculaciones($id)) {
      throw new ValidacionException('La clase tiene alumnos o profesores. Confirmá la eliminación de sus matriculaciones.');
    }

    $this->clases->transaccion(function () use ($id, $clase): void {
      $miembros = count($this->matriculas->alumnosDeClase($id)) + count($this->matriculas->profesoresDeClase($id));
      $this->matriculas->eliminarDeClase($id);
      $this->clases->eliminar($id);
      $this->auditoria->registrar(
        'clase.eliminacion',
        "Eliminación de la clase {$clase['name_class']}" . ($miembros ? " junto con {$miembros} matriculación(es)" : ''),
        'clase',
        $id,
      );
    });
  }

  /** Agrega un alumno o profesor (según su tipo) a la clase. Devuelve el usuario. */
  public function agregarMiembro(int $idClase, string $dni): array
  {
    $this->clases->buscarPorId($idClase) ?? throw new ValidacionException('No se encontró la clase.');

    $usuario = $this->usuarios->buscarPorDni(preg_replace('/\D/', '', $dni))
      ?? throw new ValidacionException('El alumno/profesor no se encuentra registrado.');

    $idUsuario = (int) $usuario['id_user'];
    if (!(int) $usuario['asset']) {
      throw new ValidacionException('El usuario está inactivo.');
    }
    if ($this->matriculas->estaEnClase($idUsuario, $idClase)) {
      throw new ValidacionException('El usuario ya se encuentra registrado en la clase.');
    }

    match (TipoUsuario::deUsuario($usuario)) {
      TipoUsuario::Alumno => $this->matriculas->inscribirAlumno($idUsuario, $idClase),
      TipoUsuario::Profesor => $this->matriculas->asignarProfesor($idUsuario, $idClase),
      TipoUsuario::Administrador => throw new ValidacionException('Los administradores no se pueden matricular en clases.'),
    };

    $clase = $this->clases->buscarPorId($idClase);
    $this->auditoria->registrar(
      'matricula.alta',
      "{$usuario['user_name']} {$usuario['user_surname']} agregado/a a {$clase['name_class']} como " . mb_strtolower(TipoUsuario::deUsuario($usuario)->etiqueta()),
      'usuario',
      $usuario['dni'],
      ['id_clase' => $idClase],
    );
    return $usuario;
  }

  public function quitarMiembro(int $idClase, int $idUsuario): void
  {
    $quitados = $this->matriculas->quitarAlumno($idUsuario, $idClase)
      + $this->matriculas->quitarProfesor($idUsuario, $idClase);

    if ($quitados === 0) {
      throw new ValidacionException('El usuario no pertenecía a la clase.');
    }

    $usuario = $this->usuarios->buscarPorId($idUsuario);
    $clase = $this->clases->buscarPorId($idClase);
    $this->auditoria->registrar(
      'matricula.baja',
      "{$usuario['user_name']} {$usuario['user_surname']} quitado/a de {$clase['name_class']}",
      'usuario',
      $usuario['dni'],
      ['id_clase' => $idClase],
    );
  }

  public function agregarHorario(int $idClase, string $dia, string $inicio, string $fin): void
  {
    $clase = $this->clases->buscarPorId($idClase) ?? throw new ValidacionException('No se encontró la clase.');
    $dia = (int) $dia;
    if ($dia < 1 || $dia > 7) {
      throw new ValidacionException('Elegí un día de la semana.');
    }
    $inicio = self::hora($inicio);
    $fin = self::hora($fin);
    if ($fin <= $inicio) {
      throw new ValidacionException('La hora de fin debe ser posterior a la de inicio.');
    }
    if ($this->horarios->haySuperposicion($idClase, $dia, $inicio, $fin)) {
      throw new ValidacionException('Ese horario se superpone con otro de la misma clase.');
    }

    $this->horarios->agregar($idClase, $dia, $inicio, $fin);
    $this->auditoria->registrar(
      'clase.horario_alta',
      sprintf('Horario de %s: %s de %s a %s', $clase['name_class'], ConfiguracionService::DIAS_SEMANA[$dia], substr($inicio, 0, 5), substr($fin, 0, 5)),
      'clase',
      $idClase,
    );
  }

  public function quitarHorario(int $idClase, int $idHorario): void
  {
    $horario = $this->horarios->buscarPorId($idHorario);
    if (!$horario || (int) $horario['id_class'] !== $idClase) {
      throw new ValidacionException('El horario no existe.');
    }
    $this->horarios->eliminar($idHorario);
    $this->auditoria->registrar(
      'clase.horario_baja',
      sprintf('Se quitó el horario de %s: %s de %s a %s', $horario['name_class'], ConfiguracionService::DIAS_SEMANA[(int) $horario['dia_semana']], substr($horario['hora_inicio'], 0, 5), substr($horario['hora_fin'], 0, 5)),
      'clase',
      $idClase,
    );
  }

  /** "9:5" o "09:05" => "09:05:00" */
  private static function hora(string $valor): string
  {
    if (!preg_match('/^(\d{1,2}):(\d{2})$/', trim($valor), $m) || (int) $m[1] > 23 || (int) $m[2] > 59) {
      throw new ValidacionException('Ingresá las horas con el formato HH:MM.');
    }
    return sprintf('%02d:%02d:00', $m[1], $m[2]);
  }

  /** @return array{0: string, 1: int} */
  private function validar(string $nombre, string $precio): array
  {
    $nombre = UsuarioService::capitalizar($nombre);
    if ($nombre === '') {
      throw new ValidacionException('Ingresá el nombre de la clase.');
    }

    $precio = str_replace(['$', '.', ' '], '', trim($precio));
    if ($precio === '' || !ctype_digit($precio)) {
      throw new ValidacionException('Ingresá un precio válido (solo números).');
    }

    return [$nombre, (int) $precio];
  }
}
