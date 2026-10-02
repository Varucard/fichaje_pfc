<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Matriculaciones: alumnos en clases (user_class) y profesores asignados (teacher_class).
 */
final class MatriculaRepository extends Repository
{
  public function alumnosDeClase(int $idClase): array
  {
    return $this->todos(
      'SELECT u.* FROM user_class uc JOIN users u ON u.id_user = uc.id_user
        WHERE uc.id_class = ? ORDER BY u.user_surname, u.user_name',
      [$idClase]
    );
  }

  public function profesoresDeClase(int $idClase): array
  {
    return $this->todos(
      'SELECT u.* FROM teacher_class tc JOIN users u ON u.id_user = tc.id_user
        WHERE tc.id_class = ? ORDER BY u.user_surname, u.user_name',
      [$idClase]
    );
  }

  public function clasesDeAlumno(int $idUsuario): array
  {
    return $this->todos(
      'SELECT c.* FROM user_class uc JOIN classes c ON c.id_class = uc.id_class
        WHERE uc.id_user = ? ORDER BY c.name_class',
      [$idUsuario]
    );
  }

  public function clasesDeProfesor(int $idUsuario): array
  {
    return $this->todos(
      'SELECT c.* FROM teacher_class tc JOIN classes c ON c.id_class = tc.id_class
        WHERE tc.id_user = ? ORDER BY c.name_class',
      [$idUsuario]
    );
  }

  /**
   * Cuota mensual (suma de precios de sus clases) de cada alumno.
   *
   * @return array<int, float> id_user => cuota
   */
  public function cuotasPorAlumno(): array
  {
    $filas = $this->todos(
      'SELECT uc.id_user, SUM(c.price_class) AS cuota FROM user_class uc
        JOIN classes c ON c.id_class = uc.id_class GROUP BY uc.id_user'
    );
    $cuotas = [];
    foreach ($filas as $fila) {
      $cuotas[(int) $fila['id_user']] = (float) $fila['cuota'];
    }
    return $cuotas;
  }

  /**
   * Clases que dicta cada profesor, con la cantidad de profesores que comparten cada clase.
   *
   * @return array<int, array> filas: id_user, id_class, name_class, profesores_en_clase
   */
  public function clasesDeProfesores(): array
  {
    return $this->todos(
      'SELECT tc.id_user, c.id_class, c.name_class,
          (SELECT COUNT(*) FROM teacher_class t2 WHERE t2.id_class = c.id_class) AS profesores_en_clase
        FROM teacher_class tc JOIN classes c ON c.id_class = tc.id_class
        ORDER BY c.name_class'
    );
  }

  public function alumnoTieneClases(int $idUsuario): bool
  {
    return (bool) $this->valor('SELECT EXISTS(SELECT 1 FROM user_class WHERE id_user = ?)', [$idUsuario]);
  }

  /** ¿El usuario está matriculado en alguna clase, como alumno o como profesor? */
  public function tieneMatriculaciones(int $idUsuario): bool
  {
    return (bool) $this->valor(
      'SELECT EXISTS(SELECT 1 FROM user_class WHERE id_user = ?)
           OR EXISTS(SELECT 1 FROM teacher_class WHERE id_user = ?)',
      [$idUsuario, $idUsuario]
    );
  }

  public function claseTieneMatriculaciones(int $idClase): bool
  {
    return (bool) $this->valor(
      'SELECT EXISTS(SELECT 1 FROM user_class WHERE id_class = ?)
           OR EXISTS(SELECT 1 FROM teacher_class WHERE id_class = ?)',
      [$idClase, $idClase]
    );
  }

  public function estaEnClase(int $idUsuario, int $idClase): bool
  {
    return (bool) $this->valor(
      'SELECT EXISTS(SELECT 1 FROM user_class WHERE id_user = ? AND id_class = ?)
           OR EXISTS(SELECT 1 FROM teacher_class WHERE id_user = ? AND id_class = ?)',
      [$idUsuario, $idClase, $idUsuario, $idClase]
    );
  }

  public function inscribirAlumno(int $idUsuario, int $idClase): void
  {
    $this->ejecutar('INSERT INTO user_class (id_user, id_class) VALUES (?, ?)', [$idUsuario, $idClase]);
  }

  public function asignarProfesor(int $idUsuario, int $idClase): void
  {
    $this->ejecutar('INSERT INTO teacher_class (id_user, id_class) VALUES (?, ?)', [$idUsuario, $idClase]);
  }

  public function quitarAlumno(int $idUsuario, int $idClase): int
  {
    return $this->ejecutar('DELETE FROM user_class WHERE id_user = ? AND id_class = ?', [$idUsuario, $idClase]);
  }

  public function quitarProfesor(int $idUsuario, int $idClase): int
  {
    return $this->ejecutar('DELETE FROM teacher_class WHERE id_user = ? AND id_class = ?', [$idUsuario, $idClase]);
  }

  public function eliminarDeClase(int $idClase): void
  {
    $this->ejecutar('DELETE FROM user_class WHERE id_class = ?', [$idClase]);
    $this->ejecutar('DELETE FROM teacher_class WHERE id_class = ?', [$idClase]);
  }
}
