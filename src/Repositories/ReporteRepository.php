<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTimeInterface;

/**
 * Consultas agregadas para reportes de caja (criterio de caja: por fecha de cobro).
 */
final class ReporteRepository extends Repository
{
  /** @return array<int, array{cantidad: int, total: float}> mes (1-12) => cuotas cobradas */
  public function cuotasPorMes(int $anio): array
  {
    $filas = $this->todos(
      'SELECT MONTH(discharge_date) AS mes, COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS total
        FROM payments WHERE YEAR(discharge_date) = ? GROUP BY MONTH(discharge_date)',
      [$anio]
    );
    $meses = [];
    foreach ($filas as $fila) {
      $meses[(int) $fila['mes']] = ['cantidad' => (int) $fila['cantidad'], 'total' => (float) $fila['total']];
    }
    return $meses;
  }

  /** @return array<int, array{cantidad: int, total: float}> mes (1-12) => ventas de productos */
  public function ventasPorMes(int $anio): array
  {
    $filas = $this->todos(
      "SELECT MONTH(fecha) AS mes, COALESCE(SUM(-cantidad), 0) AS cantidad, COALESCE(SUM(total), 0) AS total
        FROM movimientos_stock WHERE tipo = 'venta' AND YEAR(fecha) = ? GROUP BY MONTH(fecha)",
      [$anio]
    );
    $meses = [];
    foreach ($filas as $fila) {
      $meses[(int) $fila['mes']] = ['cantidad' => (int) $fila['cantidad'], 'total' => (float) $fila['total']];
    }
    return $meses;
  }

  /** Cuotas cobradas por clase entre dos fechas (según el reparto de cada pago). */
  public function cuotasPorClase(DateTimeInterface $desde, DateTimeInterface $hasta): array
  {
    return $this->todos(
      'SELECT pc.nombre_clase AS clase, COUNT(DISTINCT pc.id_payment) AS pagos, SUM(pc.monto) AS total
        FROM payment_classes pc JOIN payments p ON p.id_payment = pc.id_payment
        WHERE p.discharge_date BETWEEN ? AND ?
        GROUP BY pc.nombre_clase ORDER BY total DESC',
      [$desde->format('Y-m-d'), $hasta->format('Y-m-d')]
    );
  }

  /** Cuotas cobradas sin reparto por clase (pagos anteriores a la v3.1 o de alumnos sin clases). */
  public function cuotasSinClase(DateTimeInterface $desde, DateTimeInterface $hasta): float
  {
    return (float) $this->valor(
      'SELECT COALESCE(SUM(p.monto), 0) FROM payments p
        WHERE p.discharge_date BETWEEN ? AND ? AND NOT EXISTS (SELECT 1 FROM payment_classes pc WHERE pc.id_payment = p.id_payment)',
      [$desde->format('Y-m-d'), $hasta->format('Y-m-d')]
    );
  }

  public function ventasPorProducto(DateTimeInterface $desde, DateTimeInterface $hasta): array
  {
    return $this->todos(
      "SELECT pr.nombre AS producto, SUM(-m.cantidad) AS unidades, SUM(m.total) AS total
        FROM movimientos_stock m JOIN productos pr ON pr.id = m.id_producto
        WHERE m.tipo = 'venta' AND m.fecha BETWEEN ? AND ?
        GROUP BY pr.id ORDER BY total DESC",
      [$desde->format('Y-m-d 00:00:00'), $hasta->format('Y-m-d 23:59:59')]
    );
  }

  /** Detalle de pagos entre dos fechas (para exportar). */
  public function pagosEntre(DateTimeInterface $desde, DateTimeInterface $hasta): array
  {
    return $this->todos(
      'SELECT p.id_payment, p.discharge_date, p.date_of_renovation, p.monto, p.monto_cuota, p.meses_cubiertos,
          pr.nombre AS promocion, u.dni, u.user_name, u.user_surname,
          (SELECT GROUP_CONCAT(pc.nombre_clase SEPARATOR ", ") FROM payment_classes pc WHERE pc.id_payment = p.id_payment) AS clases
        FROM payments p JOIN users u ON u.id_user = p.id_user
        LEFT JOIN promociones pr ON pr.id = p.id_promocion
        WHERE p.discharge_date BETWEEN ? AND ?
        ORDER BY p.discharge_date, p.id_payment',
      [$desde->format('Y-m-d'), $hasta->format('Y-m-d')]
    );
  }

  public function liquidacionesDelPeriodo(string $periodo): array
  {
    return $this->todos(
      'SELECT l.*, u.dni, u.user_name, u.user_surname FROM liquidaciones l
        JOIN users u ON u.id_user = l.id_profesor WHERE l.periodo = ? ORDER BY u.user_surname, u.user_name',
      [$periodo]
    );
  }
}
