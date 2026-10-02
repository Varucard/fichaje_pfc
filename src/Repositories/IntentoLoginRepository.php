<?php

declare(strict_types=1);

namespace App\Repositories;

final class IntentoLoginRepository extends Repository
{
  public function obtener(string $clave): ?array
  {
    return $this->uno('SELECT * FROM intentos_login WHERE clave = ?', [$clave]);
  }

  /**
   * Suma un intento fallido en una sola sentencia atómica (sin carreras entre pedidos
   * simultáneos). Si el primer fallo quedó fuera de la ventana, el conteo vuelve a 1;
   * al llegar al máximo, la clave queda bloqueada.
   * MySQL evalúa las asignaciones en orden: bloqueado_hasta ve el nuevo valor de fallidos.
   */
  public function registrarFallo(string $clave, int $maximo, int $ventanaMinutos, int $bloqueoMinutos): array
  {
    $this->ejecutar(
      'INSERT INTO intentos_login (clave, fallidos, primer_fallo, bloqueado_hasta)
        VALUES (?, 1, NOW(), IF(1 >= ?, NOW() + INTERVAL ? MINUTE, NULL))
        ON DUPLICATE KEY UPDATE
          fallidos = IF(primer_fallo < NOW() - INTERVAL ? MINUTE, 1, fallidos + 1),
          primer_fallo = IF(primer_fallo < NOW() - INTERVAL ? MINUTE, NOW(), primer_fallo),
          bloqueado_hasta = IF(fallidos >= ?, NOW() + INTERVAL ? MINUTE, bloqueado_hasta)',
      [$clave, $maximo, $bloqueoMinutos, $ventanaMinutos, $ventanaMinutos, $maximo, $bloqueoMinutos]
    );
    return $this->obtener($clave) ?? [];
  }

  public function borrar(string $clave): void
  {
    $this->ejecutar('DELETE FROM intentos_login WHERE clave = ?', [$clave]);
  }
}
