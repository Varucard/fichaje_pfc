<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\TipoUsuario;
use App\Exceptions\ValidacionException;
use App\Repositories\ClaseRepository;
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
    ];
  }

  /**
   * Crea una clase y le asigna los profesores elegidos.
   *
   * @param array<int, string|int> $idsProfesores
   * @return array{id: int, omitidos: array<int, string>}
   */
  public function crear(string $nombre, string $precio, array $idsProfesores): array
  {
    [$nombre, $precio] = $this->validar($nombre, $precio);

    if ($this->clases->buscarPorNombreExacto($nombre)) {
      throw new ValidacionException("La clase \"{$nombre}\" ya existe.");
    }

    return $this->clases->transaccion(function () use ($nombre, $precio, $idsProfesores): array {
      $id = $this->clases->crear($nombre, $precio);
      $omitidos = [];

      foreach (array_unique(array_map('intval', $idsProfesores)) as $idProfesor) {
        $profesor = $this->usuarios->buscarPorId($idProfesor);
        if (!$profesor || TipoUsuario::deUsuario($profesor) !== TipoUsuario::Profesor || !(int) $profesor['asset']) {
          $omitidos[] = $profesor['user_name'] ?? "ID {$idProfesor}";
          continue;
        }
        $this->matriculas->asignarProfesor($idProfesor, $id);
      }

      return ['id' => $id, 'omitidos' => $omitidos];
    });
  }

  public function actualizar(int $id, string $nombre, string $precio): void
  {
    $this->clases->buscarPorId($id) ?? throw new ValidacionException('No se encontró la clase.');
    [$nombre, $precio] = $this->validar($nombre, $precio);

    $otra = $this->clases->buscarPorNombreExacto($nombre);
    if ($otra && (int) $otra['id_class'] !== $id) {
      throw new ValidacionException("Ya existe otra clase llamada \"{$nombre}\".");
    }

    $this->clases->actualizar($id, $nombre, $precio);
  }

  /**
   * Elimina una clase. Si tiene alumnos o profesores, solo se elimina cuando
   * $incluirMatriculaciones es true (el usuario lo confirmó).
   */
  public function eliminar(int $id, bool $incluirMatriculaciones): void
  {
    $this->clases->buscarPorId($id) ?? throw new ValidacionException('No se encontró la clase.');

    if (!$incluirMatriculaciones && $this->matriculas->claseTieneMatriculaciones($id)) {
      throw new ValidacionException('La clase tiene alumnos o profesores. Confirmá la eliminación de sus matriculaciones.');
    }

    $this->clases->transaccion(function () use ($id): void {
      $this->matriculas->eliminarDeClase($id);
      $this->clases->eliminar($id);
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

    return $usuario;
  }

  public function quitarMiembro(int $idClase, int $idUsuario): void
  {
    $quitados = $this->matriculas->quitarAlumno($idUsuario, $idClase)
      + $this->matriculas->quitarProfesor($idUsuario, $idClase);

    if ($quitados === 0) {
      throw new ValidacionException('El usuario no pertenecía a la clase.');
    }
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
