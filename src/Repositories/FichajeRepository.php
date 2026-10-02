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
      CONCAT_WS(' ', u.user_name, u.user_surname) AS alumno,
      (SELECT MAX(p.date_of_renovation) FROM payments p WHERE p.id_user = i.id_user) AS date_of_renovation
    FROM incomes i
    JOIN users u ON u.id_user = i.id_user";

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

  /** Últimos ingresos de un usuario. */
  public function deUsuario(int $idUsuario, int $limite = 10): array
  {
    $stmt = $this->pdo->prepare('SELECT addmission_date FROM incomes WHERE id_user = :id ORDER BY addmission_date DESC LIMIT :limite');
    $stmt->bindValue('id', $idUsuario, PDO::PARAM_INT);
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
  }

  /** ¿El usuario fichó después de la fecha indicada? */
  public function fichoDesde(int $idUsuario, DateTimeInterface $desde): bool
  {
    return (bool) $this->valor(
      'SELECT EXISTS(SELECT 1 FROM incomes WHERE id_user = ? AND addmission_date >= ?)',
      [$idUsuario, $desde->format('Y-m-d H:i:s')]
    );
  }

  public function registrar(int $idUsuario, DateTimeInterface $fecha): void
  {
    $this->ejecutar(
      'INSERT INTO incomes (id_user, addmission_date) VALUES (?, ?)',
      [$idUsuario, $fecha->format('Y-m-d H:i:s')]
    );
  }
}
