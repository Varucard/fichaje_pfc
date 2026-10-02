<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Horarios semanales de las clases (tabla clase_horarios). dia_semana: 1 = lunes ... 7 = domingo.
 */
final class HorarioRepository extends Repository
{
  public function deClase(int $idClase): array
  {
    return $this->todos(
      'SELECT * FROM clase_horarios WHERE id_class = ? ORDER BY dia_semana, hora_inicio',
      [$idClase]
    );
  }

  /** Horarios de un día de todas las clases en las que está inscripto el alumno. */
  public function delAlumnoEnDia(int $idUsuario, int $diaSemana): array
  {
    return $this->todos(
      'SELECT h.*, c.name_class FROM clase_horarios h
        JOIN user_class uc ON uc.id_class = h.id_class
        JOIN classes c ON c.id_class = h.id_class
        WHERE uc.id_user = ? AND h.dia_semana = ?',
      [$idUsuario, $diaSemana]
    );
  }

  /** Clases del alumno que todavía no tienen ningún horario cargado. */
  public function clasesDelAlumnoSinHorario(int $idUsuario): array
  {
    return $this->todos(
      'SELECT c.id_class, c.name_class FROM user_class uc JOIN classes c ON c.id_class = uc.id_class
        WHERE uc.id_user = ? AND NOT EXISTS (SELECT 1 FROM clase_horarios h WHERE h.id_class = c.id_class)',
      [$idUsuario]
    );
  }

  public function buscarPorId(int $id): ?array
  {
    return $this->uno('SELECT h.*, c.name_class FROM clase_horarios h JOIN classes c ON c.id_class = h.id_class WHERE h.id = ?', [$id]);
  }

  public function haySuperposicion(int $idClase, int $dia, string $inicio, string $fin): bool
  {
    return (bool) $this->valor(
      'SELECT EXISTS(SELECT 1 FROM clase_horarios WHERE id_class = ? AND dia_semana = ? AND hora_inicio < ? AND hora_fin > ?)',
      [$idClase, $dia, $fin, $inicio]
    );
  }

  public function agregar(int $idClase, int $dia, string $inicio, string $fin): int
  {
    $this->ejecutar(
      'INSERT INTO clase_horarios (id_class, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)',
      [$idClase, $dia, $inicio, $fin]
    );
    return (int) $this->pdo->lastInsertId();
  }

  public function eliminar(int $id): void
  {
    $this->ejecutar('DELETE FROM clase_horarios WHERE id = ?', [$id]);
  }
}
