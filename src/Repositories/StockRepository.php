<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTimeInterface;
use PDO;

final class StockRepository extends Repository
{
  public function listar(bool $incluirInactivos = true): array
  {
    return $this->todos(
      'SELECT * FROM productos' . ($incluirInactivos ? '' : ' WHERE activo = 1') . ' ORDER BY activo DESC, nombre'
    );
  }

  public function buscarPorId(int $id): ?array
  {
    return $this->uno('SELECT * FROM productos WHERE id = ?', [$id]);
  }

  /** Bloquea la fila hasta el fin de la transacción (evita ventas simultáneas sin stock). */
  public function buscarParaActualizar(int $id): ?array
  {
    return $this->uno('SELECT * FROM productos WHERE id = ? FOR UPDATE', [$id]);
  }

  public function buscarPorNombre(string $nombre): ?array
  {
    return $this->uno('SELECT * FROM productos WHERE nombre = ?', [$nombre]);
  }

  public function crear(array $datos): int
  {
    $this->ejecutar(
      'INSERT INTO productos (nombre, descripcion, precio, stock, stock_minimo, creado_en)
        VALUES (:nombre, :descripcion, :precio, 0, :stock_minimo, NOW())',
      $datos
    );
    return (int) $this->pdo->lastInsertId();
  }

  public function actualizar(int $id, array $datos): void
  {
    $this->ejecutar(
      'UPDATE productos SET nombre = :nombre, descripcion = :descripcion, precio = :precio, stock_minimo = :stock_minimo
        WHERE id = :id',
      $datos + ['id' => $id]
    );
  }

  public function cambiarActivo(int $id, bool $activo): void
  {
    $this->ejecutar('UPDATE productos SET activo = ? WHERE id = ?', [$activo ? 1 : 0, $id]);
  }

  public function fijarStock(int $id, int $stock): void
  {
    $this->ejecutar('UPDATE productos SET stock = ? WHERE id = ?', [$stock, $id]);
  }

  public function registrarMovimiento(array $datos): int
  {
    $this->ejecutar(
      'INSERT INTO movimientos_stock
          (id_producto, tipo, cantidad, stock_resultante, precio_unitario, total, id_cliente, observacion, id_admin, fecha)
        VALUES (:id_producto, :tipo, :cantidad, :stock_resultante, :precio_unitario, :total, :id_cliente, :observacion, :id_admin, NOW())',
      $datos
    );
    return (int) $this->pdo->lastInsertId();
  }

  public function movimientosDe(int $idProducto, int $limite = 50): array
  {
    $stmt = $this->pdo->prepare(
      "SELECT m.*, CONCAT_WS(' ', c.user_name, c.user_surname) AS cliente, c.dni AS dni_cliente
        FROM movimientos_stock m LEFT JOIN users c ON c.id_user = m.id_cliente
        WHERE m.id_producto = :id ORDER BY m.fecha DESC, m.id DESC LIMIT :limite"
    );
    $stmt->bindValue('id', $idProducto, PDO::PARAM_INT);
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  /** @return array{cantidad: int, total: float} */
  public function ventasEntre(DateTimeInterface $desde, DateTimeInterface $hasta): array
  {
    $fila = $this->uno(
      "SELECT COALESCE(SUM(-cantidad), 0) AS cantidad, COALESCE(SUM(total), 0) AS total
        FROM movimientos_stock WHERE tipo = 'venta' AND fecha BETWEEN ? AND ?",
      [$desde->format('Y-m-d 00:00:00'), $hasta->format('Y-m-d 23:59:59')]
    );
    return ['cantidad' => (int) $fila['cantidad'], 'total' => (float) $fila['total']];
  }

  public function cantidadBajoMinimo(): int
  {
    return (int) $this->valor('SELECT COUNT(*) FROM productos WHERE activo = 1 AND stock <= stock_minimo');
  }
}
