<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTimeInterface;
use PDO;

/**
 * Ingresos al gimnasio (tabla incomes).
 */
final class FichajeRepository extends Repository
{
  private const SELECT = "
    SELECT i.id_income, i.id_user, i.addmission_date, u.rfid, u.dni,
      CONCAT_WS(' ', u.user_name, u.user_surname) AS alumno, c.name_class AS clase,
      (SELECT MAX(p.date_of_renovation) FROM payments p WHERE p.id_user = i.id_user) AS date_of_renovation
    FROM incomes i
    JOIN users u ON u.id_user = i.id_user
    LEFT JOIN classes c ON c.id_class = i.id_class";

  public function ultimos(int $limite = 10): array
  {
    $stmt = $this->pdo->prepare(self::SELECT . ' ORDER BY i.addmission_date DESC LIMIT :limite');
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  /** Busca por nombre, apellido, DNI o llavero. */
  public function buscar(string $termino, int $limite = 10): array
  {
    $stmt = $this->pdo->prepare(self::SELECT . "
      WHERE u.user_name LIKE :t1 OR u.user_surname LIKE :t2 OR u.dni LIKE :t3 OR u.rfid LIKE :t4
         OR CONCAT_WS(' ', u.user_name, u.user_surname) LIKE :t5
      ORDER BY i.addmission_date DESC LIMIT :limite");

    $like = '%' . $termino . '%';
    foreach (['t1', 't2', 't3', 't4', 't5'] as $parametro) {
      $stmt->bindValue($parametro, $like);
    }
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  /** @return array<int, string> id_user => fecha y hora de su última fichada */
  public function ultimaPorUsuario(): array
  {
    $filas = $this->todos('SELECT id_user, MAX(addmission_date) AS ultima FROM incomes GROUP BY id_user');
    return array_column($filas, 'ultima', 'id_user');
  }

  public function contarEntre(DateTimeInterface $desde, DateTimeInterface $hasta): int
  {
    return (int) $this->valor(
      'SELECT COUNT(*) FROM incomes WHERE addmission_date BETWEEN ? AND ?',
      [$desde->format('Y-m-d 00:00:00'), $hasta->format('Y-m-d 23:59:59')]
    );
  }

  /** Últimos ingresos de un usuario. */
  public function deUsuario(int $idUsuario, int $limite = 10): array
  {
    $stmt = $this->pdo->prepare(
      'SELECT i.addmission_date, c.name_class AS clase FROM incomes i LEFT JOIN classes c ON c.id_class = i.id_class
        WHERE i.id_user = :id ORDER BY i.addmission_date DESC LIMIT :limite'
    );
    $stmt->bindValue('id', $idUsuario, PDO::PARAM_INT);
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  /** ¿El usuario fichó después de la fecha indicada? */
  public function fichoDesde(int $idUsuario, DateTimeInterface $desde): bool
  {
    return (bool) $this->valor(
      'SELECT EXISTS(SELECT 1 FROM incomes WHERE id_user = ? AND addmission_date >= ?)',
      [$idUsuario, $desde->format('Y-m-d H:i:s')]
    );
  }

  public function registrar(int $idUsuario, DateTimeInterface $fecha, ?int $idClase = null): void
  {
    $this->ejecutar(
      'INSERT INTO incomes (id_user, addmission_date, id_class) VALUES (?, ?, ?)',
      [$idUsuario, $fecha->format('Y-m-d H:i:s'), $idClase]
    );
  }

  /**
   * Asistencias por clase entre dos fechas (fichadas con clase deducida por horario).
   * Cuenta una asistencia por alumno, clase y día: si fichó con el llavero y además se
   * le cargó una fichada manual, cuenta una sola vez.
   *
   * @return array<int, int> id_class => cantidad
   */
  public function asistenciasPorClase(DateTimeInterface $desde, DateTimeInterface $hasta): array
  {
    $filas = $this->todos(
      'SELECT id_class, COUNT(DISTINCT id_user, DATE(addmission_date)) AS cantidad FROM incomes
        WHERE id_class IS NOT NULL AND addmission_date BETWEEN ? AND ? GROUP BY id_class',
      [$desde->format('Y-m-d 00:00:00'), $hasta->format('Y-m-d 23:59:59')]
    );
    return array_map('intval', array_column($filas, 'cantidad', 'id_class'));
  }

  /** Asistencias (alumno y día distintos) a una clase en los últimos N días. */
  public function asistenciasAClase(int $idClase, int $dias = 30): int
  {
    return (int) $this->valor(
      'SELECT COUNT(DISTINCT id_user, DATE(addmission_date)) FROM incomes WHERE id_class = ? AND addmission_date >= NOW() - INTERVAL ? DAY',
      [$idClase, $dias]
    );
  }
}
