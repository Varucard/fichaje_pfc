<?php

declare(strict_types=1);

namespace App\Repositories;

use DateTimeInterface;
use PDO;

final class PagoRepository extends Repository
{
  /** Pagos más recientes del usuario, del más nuevo al más viejo. */
  public function ultimosDeUsuario(int $idUsuario, int $limite = 5): array
  {
    $stmt = $this->pdo->prepare(
      'SELECT p.id_payment, p.id_user, p.discharge_date, p.date_of_renovation, p.monto, p.monto_cuota,
          p.meses_cubiertos, pr.nombre AS promocion
        FROM payments p LEFT JOIN promociones pr ON pr.id = p.id_promocion
        WHERE p.id_user = :id ORDER BY p.discharge_date DESC, p.id_payment DESC LIMIT :limite'
    );
    $stmt->bindValue('id', $idUsuario, PDO::PARAM_INT);
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  /** Fecha de vencimiento más lejana entre los pagos del usuario (Y-m-d) o null si nunca pagó. */
  public function ultimaRenovacion(int $idUsuario): ?string
  {
    $valor = $this->valor('SELECT MAX(date_of_renovation) FROM payments WHERE id_user = ?', [$idUsuario]);
    return $valor === null ? null : (string) $valor;
  }

  /** Suma de lo que quedó sin cobrar en pagos parciales (cuota - monto). */
  public function saldoPendiente(int $idUsuario): float
  {
    return (float) $this->valor(
      'SELECT COALESCE(SUM(GREATEST(monto_cuota - monto, 0)), 0) FROM payments
        WHERE id_user = ? AND monto IS NOT NULL AND monto_cuota IS NOT NULL',
      [$idUsuario]
    );
  }

  /**
   * Datos de pago de todos los alumnos activos en una sola consulta (para el listado de deudores).
   *
   * @return array<int, array{id_user: int, renovacion: ?string, saldo: float}>
   */
  public function resumenPorAlumno(): array
  {
    $filas = $this->todos(
      'SELECT id_user, MAX(date_of_renovation) AS renovacion,
          COALESCE(SUM(CASE WHEN monto IS NOT NULL AND monto_cuota IS NOT NULL
                            THEN GREATEST(monto_cuota - monto, 0) END), 0) AS saldo
        FROM payments GROUP BY id_user'
    );
    $resumen = [];
    foreach ($filas as $fila) {
      $resumen[(int) $fila['id_user']] = [
        'id_user' => (int) $fila['id_user'],
        'renovacion' => $fila['renovacion'],
        'saldo' => (float) $fila['saldo'],
      ];
    }
    return $resumen;
  }

  /** @return array{cantidad: int, total: float} Pagos registrados entre dos fechas. */
  public function totalEntre(DateTimeInterface $desde, DateTimeInterface $hasta): array
  {
    $fila = $this->uno(
      'SELECT COUNT(*) AS cantidad, COALESCE(SUM(monto), 0) AS total FROM payments WHERE discharge_date BETWEEN ? AND ?',
      [$desde->format('Y-m-d'), $hasta->format('Y-m-d')]
    );
    return ['cantidad' => (int) $fila['cantidad'], 'total' => (float) $fila['total']];
  }

  public function buscarPorId(int $id): ?array
  {
    return $this->uno('SELECT * FROM payments WHERE id_payment = ?', [$id]);
  }

  public function crear(
    int $idUsuario,
    DateTimeInterface $fechaPago,
    DateTimeInterface $fechaRenovacion,
    float $monto,
    float $cuota,
    int $mesesCubiertos = 1,
    ?int $idPromocion = null,
  ): int {
    $this->ejecutar(
      'INSERT INTO payments (id_user, discharge_date, date_of_renovation, monto, monto_cuota, meses_cubiertos, id_promocion)
        VALUES (?, ?, ?, ?, ?, ?, ?)',
      [$idUsuario, $fechaPago->format('Y-m-d'), $fechaRenovacion->format('Y-m-d'), $monto, $cuota, $mesesCubiertos, $idPromocion]
    );
    return (int) $this->pdo->lastInsertId();
  }

  /** @param array{id_class: int, nombre_clase: string, precio_clase: float, monto: float} $parte */
  public function agregarDetalle(int $idPago, array $parte): void
  {
    $this->ejecutar(
      'INSERT INTO payment_classes (id_payment, id_class, nombre_clase, precio_clase, monto) VALUES (?, ?, ?, ?, ?)',
      [$idPago, $parte['id_class'], $parte['nombre_clase'], $parte['precio_clase'], $parte['monto']]
    );
  }

  /**
   * Lo cobrado por cada clase que corresponde a un mes (AAAA-MM).
   * Un pago de varios meses se reparte en partes iguales entre los meses que cubre,
   * empezando por el mes del pago (un pago de 3 meses suma 1/3 a cada mes).
   *
   * @return array<int, float> id_class => monto
   */
  public function cobradoPorClase(string $periodo): array
  {
    [$anio, $mes] = array_map('intval', explode('-', $periodo));
    $filas = $this->todos(
      'SELECT pc.id_class, SUM(pc.monto / p.meses_cubiertos) AS total
        FROM payment_classes pc JOIN payments p ON p.id_payment = pc.id_payment
        WHERE pc.id_class IS NOT NULL
          AND ? BETWEEN (YEAR(p.discharge_date) * 12 + MONTH(p.discharge_date))
                    AND (YEAR(p.discharge_date) * 12 + MONTH(p.discharge_date) + p.meses_cubiertos - 1)
        GROUP BY pc.id_class',
      [$anio * 12 + $mes]
    );
    return array_column(array_map(fn ($f) => ['id' => (int) $f['id_class'], 'total' => (float) $f['total']], $filas), 'total', 'id');
  }

  /** Texto del plan de un pago para el comprobante. */
  public function descripcionPlan(array $pago): string
  {
    $meses = (int) ($pago['meses_cubiertos'] ?? 1);
    $texto = $meses === 1 ? '1 mes' : "{$meses} meses";
    if (!empty($pago['id_promocion'])) {
      $nombre = $this->valor('SELECT nombre FROM promociones WHERE id = ?', [$pago['id_promocion']]);
      $texto .= $nombre ? " — Promoción {$nombre}" : '';
    }
    return $texto;
  }

  /** Reparto del pago por clase. */
  public function detalle(int $idPago): array
  {
    return $this->todos('SELECT nombre_clase, precio_clase, monto FROM payment_classes WHERE id_payment = ? ORDER BY id_payment_class', [$idPago]);
  }

  public function eliminar(int $id): void
  {
    $this->ejecutar('DELETE FROM payments WHERE id_payment = ?', [$id]);
  }
}
