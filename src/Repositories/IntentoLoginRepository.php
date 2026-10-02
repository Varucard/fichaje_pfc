<?php

declare(strict_types=1);

namespace App\Repositories;

final class IntentoLoginRepository extends Repository
{
  public function obtener(string $clave): ?array
  {
    return $this->uno('SELECT * FROM intentos_login WHERE clave = ?', [$clave]);
  }

  public function guardar(string $clave, int $fallidos, string $primerFallo, ?string $bloqueadoHasta): void
  {
    $this->ejecutar(
      'REPLACE INTO intentos_login (clave, fallidos, primer_fallo, bloqueado_hasta) VALUES (?, ?, ?, ?)',
      [$clave, $fallidos, $primerFallo, $bloqueadoHasta]
    );
  }

  public function borrar(string $clave): void
  {
    $this->ejecutar('DELETE FROM intentos_login WHERE clave = ?', [$clave]);
  }
}
