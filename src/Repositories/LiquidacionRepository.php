<?php

declare(strict_types=1);

namespace App\Repositories;

final class LiquidacionRepository extends Repository
{
  /** @return array<int, array> id_profesor => liquidación */
  public function delPeriodo(string $periodo): array
  {
    $filas = $this->todos('SELECT * FROM liquidaciones WHERE periodo = ?', [$periodo]);
    return array_column($filas, null, 'id_profesor');
  }

  public function deProfesor(int $idProfesor, int $limite = 12): array
  {
    return $this->todos(
      "SELECT * FROM liquidaciones WHERE id_profesor = ? ORDER BY periodo DESC LIMIT {$limite}",
      [$idProfesor]
    );
  }

  public function buscarPorId(int $id): ?array
  {
    return $this->uno(
      'SELECT l.*, u.user_name, u.user_surname, u.dni FROM liquidaciones l
        JOIN users u ON u.id_user = l.id_profesor WHERE l.id = ?',
      [$id]
    );
  }

  public function existe(int $idProfesor, string $periodo): bool
  {
    return (bool) $this->valor('SELECT EXISTS(SELECT 1 FROM liquidaciones WHERE id_profesor = ? AND periodo = ?)', [$idProfesor, $periodo]);
  }

  public function crear(array $datos): int
  {
    $this->ejecutar(
      'INSERT INTO liquidaciones (id_profesor, periodo, monto_base, porcentaje, monto, detalle, fecha_registro, id_admin)
        VALUES (:id_profesor, :periodo, :monto_base, :porcentaje, :monto, :detalle, NOW(), :id_admin)',
      $datos
    );
    return (int) $this->pdo->lastInsertId();
  }

  public function marcarPagada(int $id): void
  {
    $this->ejecutar('UPDATE liquidaciones SET pagada = 1, fecha_pago = NOW() WHERE id = ?', [$id]);
  }

  public function eliminar(int $id): void
  {
    $this->ejecutar('DELETE FROM liquidaciones WHERE id = ?', [$id]);
  }
}
