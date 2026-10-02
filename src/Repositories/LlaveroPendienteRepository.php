<?php

declare(strict_types=1);

namespace App\Repositories;

/**
 * Llaveros desconocidos leídos por el Arduino (tabla uid_incomes), a la espera de que
 * el panel los muestre para poder asignarlos a un usuario.
 */
final class LlaveroPendienteRepository extends Repository
{
  /** Lo deja pendiente si todavía no lo está. Devuelve true si es nuevo. */
  public function registrar(string $uid): bool
  {
    if ($this->valor('SELECT EXISTS(SELECT 1 FROM uid_incomes WHERE uid = ?)', [$uid])) {
      return false;
    }
    $this->ejecutar('INSERT INTO uid_incomes (uid) VALUES (?)', [$uid]);
    return true;
  }

  /** Devuelve los llaveros pendientes y los marca como vistos (los borra). */
  public function tomarPendientes(): array
  {
    return $this->transaccion(function (): array {
      $filas = $this->todos('SELECT id_uid_incomes, uid FROM uid_incomes ORDER BY id_uid_incomes FOR UPDATE');
      if ($filas) {
        $ids = array_column($filas, 'id_uid_incomes');
        $marcadores = implode(',', array_fill(0, count($ids), '?'));
        $this->ejecutar("DELETE FROM uid_incomes WHERE id_uid_incomes IN ({$marcadores})", $ids);
      }
      return array_column($filas, 'uid');
    });
  }
}
