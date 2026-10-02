<?php

declare(strict_types=1);

namespace App\Repositories;

final class PromocionRepository extends Repository
{
  public function listar(bool $soloActivas = false): array
  {
    return $this->todos(
      'SELECT p.*, (SELECT COUNT(*) FROM payments WHERE id_promocion = p.id) AS usos FROM promociones p'
        . ($soloActivas ? ' WHERE p.activa = 1' : '') . ' ORDER BY p.activa DESC, p.meses_pagos, p.nombre'
    );
  }

  public function buscarPorId(int $id): ?array
  {
    return $this->uno('SELECT * FROM promociones WHERE id = ?', [$id]);
  }

  public function buscarPorNombre(string $nombre): ?array
  {
    return $this->uno('SELECT * FROM promociones WHERE nombre = ?', [$nombre]);
  }

  public function crear(array $datos): int
  {
    $this->ejecutar(
      'INSERT INTO promociones (nombre, meses_pagos, meses_bonificados, descuento, creada_en)
        VALUES (:nombre, :meses_pagos, :meses_bonificados, :descuento, NOW())',
      $datos
    );
    return (int) $this->pdo->lastInsertId();
  }

  public function cambiarActiva(int $id, bool $activa): void
  {
    $this->ejecutar('UPDATE promociones SET activa = ? WHERE id = ?', [$activa ? 1 : 0, $id]);
  }
}
