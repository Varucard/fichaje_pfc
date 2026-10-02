<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTimeInterface;

final class PagoRepository extends Repository
{
  /** Pagos más recientes del usuario, del más nuevo al más viejo. */
  public function ultimosDeUsuario(int $idUsuario, int $limite = 5): array
  {
    $stmt = $this->pdo->prepare(
      'SELECT id_payment, id_user, discharge_date, date_of_renovation FROM payments
        WHERE id_user = :id ORDER BY discharge_date DESC, id_payment DESC LIMIT :limite'
    );
    $stmt->bindValue('id', $idUsuario, \PDO::PARAM_INT);
    $stmt->bindValue('limite', $limite, \PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  /** Fecha de vencimiento más lejana entre los pagos del usuario (Y-m-d) o null si nunca pagó. */
  public function ultimaRenovacion(int $idUsuario): ?string
  {
    $valor = $this->valor('SELECT MAX(date_of_renovation) FROM payments WHERE id_user = ?', [$idUsuario]);
    return $valor === null ? null : (string) $valor;
  }

  public function buscarPorId(int $id): ?array
  {
    return $this->uno('SELECT * FROM payments WHERE id_payment = ?', [$id]);
  }

  public function crear(int $idUsuario, DateTimeInterface $fechaPago, DateTimeInterface $fechaRenovacion): int
  {
    $this->ejecutar(
      'INSERT INTO payments (id_user, discharge_date, date_of_renovation) VALUES (?, ?, ?)',
      [$idUsuario, $fechaPago->format('Y-m-d'), $fechaRenovacion->format('Y-m-d')]
    );
    return (int) $this->pdo->lastInsertId();
  }

  public function eliminar(int $id): void
  {
    $this->ejecutar('DELETE FROM payments WHERE id_payment = ?', [$id]);
  }
}
