<?php

declare(strict_types=1);

namespace App\Repositories;

final class ClaseRepository extends Repository
{
  /**
   * Clases con los nombres de sus profesores y la cantidad de matriculaciones,
   * en una sola consulta (antes se hacía una consulta por cada clase y profesor).
   */
  private const SELECT_RESUMEN = "
    SELECT c.id_class, c.name_class, c.price_class,
      GROUP_CONCAT(DISTINCT CONCAT_WS(' ', u.user_name, u.user_surname) ORDER BY u.user_name SEPARATOR ', ') AS profesores,
      (SELECT COUNT(*) FROM teacher_class t WHERE t.id_class = c.id_class)
        + (SELECT COUNT(*) FROM user_class a WHERE a.id_class = c.id_class) AS matriculaciones
    FROM classes c
    LEFT JOIN teacher_class tc ON tc.id_class = c.id_class
    LEFT JOIN users u ON u.id_user = tc.id_user";

  public function listar(): array
  {
    return $this->todos(self::SELECT_RESUMEN . ' GROUP BY c.id_class ORDER BY c.name_class');
  }

  public function buscarPorNombre(string $termino): array
  {
    return $this->todos(
      self::SELECT_RESUMEN . ' WHERE c.name_class LIKE ? GROUP BY c.id_class ORDER BY c.name_class',
      ['%' . $termino . '%']
    );
  }

  public function buscarPorId(int $id): ?array
  {
    return $this->uno('SELECT * FROM classes WHERE id_class = ?', [$id]);
  }

  public function buscarPorNombreExacto(string $nombre): ?array
  {
    return $this->uno('SELECT * FROM classes WHERE name_class = ?', [$nombre]);
  }

  public function crear(string $nombre, int $precio): int
  {
    $this->ejecutar('INSERT INTO classes (name_class, price_class) VALUES (?, ?)', [$nombre, $precio]);
    return (int) $this->pdo->lastInsertId();
  }

  public function actualizar(int $id, string $nombre, int $precio): void
  {
    $this->ejecutar('UPDATE classes SET name_class = ?, price_class = ? WHERE id_class = ?', [$nombre, $precio, $id]);
  }

  public function eliminar(int $id): void
  {
    $this->ejecutar('DELETE FROM classes WHERE id_class = ?', [$id]);
  }
}
