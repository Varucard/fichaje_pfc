<?php

declare(strict_types=1);

namespace App\Repositories;

use App\Domain\TipoUsuario;
use DateTimeInterface;

final class UsuarioRepository extends Repository
{
  public function buscarPorId(int $id): ?array
  {
    return $this->uno('SELECT * FROM users WHERE id_user = ?', [$id]);
  }

  public function buscarPorDni(string $dni): ?array
  {
    return $this->uno('SELECT * FROM users WHERE dni = ?', [$dni]);
  }

  /** Usuario ACTIVO que tiene asignado el llavero. */
  public function buscarActivoPorRfid(string $rfid): ?array
  {
    return $this->uno('SELECT * FROM users WHERE rfid = ? AND asset = 1 LIMIT 1', [$rfid]);
  }

  /** Último usuario (activo o no) que tuvo registrado el llavero. */
  public function buscarUltimoPorRfid(string $rfid): ?array
  {
    return $this->uno('SELECT * FROM users WHERE rfid = ? ORDER BY id_user DESC LIMIT 1', [$rfid]);
  }

  /** Búsqueda parcial por nombre, apellido o nombre completo. */
  public function buscarPorNombre(string $termino): array
  {
    $like = '%' . $termino . '%';
    return $this->todos(
      "SELECT * FROM users
        WHERE user_name LIKE ? OR user_surname LIKE ? OR CONCAT_WS(' ', user_name, user_surname) LIKE ?
        ORDER BY user_surname, user_name",
      [$like, $like, $like]
    );
  }

  public function listarPorTipo(TipoUsuario $tipo, bool $soloActivos = false): array
  {
    $sql = 'SELECT * FROM users WHERE type_user = ?' . ($soloActivos ? ' AND asset = 1' : '');
    return $this->todos($sql . ' ORDER BY user_surname, user_name', [$tipo->value]);
  }

  public function cumpleanerosDel(DateTimeInterface $fecha): array
  {
    return $this->todos(
      "SELECT id_user, user_name, user_surname FROM users
        WHERE asset = 1 AND DATE_FORMAT(birth_day, '%m-%d') = ?",
      [$fecha->format('m-d')]
    );
  }

  public function crear(array $datos): int
  {
    $this->ejecutar(
      'INSERT INTO users (rfid, dni, user_name, user_surname, birth_day, email, phone_number, type_user)
        VALUES (:rfid, :dni, :nombre, :apellido, :nacimiento, :email, :telefono, :tipo)',
      $this->parametros($datos)
    );
    return (int) $this->pdo->lastInsertId();
  }

  public function actualizar(int $id, array $datos): void
  {
    $this->ejecutar(
      'UPDATE users SET rfid = :rfid, dni = :dni, user_name = :nombre, user_surname = :apellido,
          birth_day = :nacimiento, email = :email, phone_number = :telefono, type_user = :tipo
        WHERE id_user = :id',
      $this->parametros($datos) + ['id' => $id]
    );
  }

  public function cambiarEstado(int $id, bool $activo): void
  {
    $this->ejecutar('UPDATE users SET asset = ? WHERE id_user = ?', [$activo ? 1 : 0, $id]);
  }

  public function asignarLlavero(int $id, string $rfid): void
  {
    $this->ejecutar('UPDATE users SET rfid = ? WHERE id_user = ?', [$rfid, $id]);
  }

  public function actualizarLiquidacion(int $id, ?string $modo, ?float $porcentaje, ?float $montoPorAsistencia): void
  {
    $this->ejecutar(
      'UPDATE users SET modo_liquidacion = ?, porcentaje_liquidacion = ?, monto_por_asistencia = ? WHERE id_user = ?',
      [$modo, $porcentaje, $montoPorAsistencia, $id]
    );
  }

  /** Alumnos activos con email que aceptan avisos. */
  public function alumnosParaAvisos(): array
  {
    return $this->todos(
      "SELECT * FROM users WHERE type_user = ? AND asset = 1 AND acepta_emails = 1 AND email IS NOT NULL AND email <> ''",
      [TipoUsuario::Alumno->value]
    );
  }

  public function asignarTokenBaja(int $id, string $token): void
  {
    $this->ejecutar('UPDATE users SET token_baja = ? WHERE id_user = ? AND token_baja IS NULL', [$token, $id]);
  }

  public function buscarPorTokenBaja(string $token): ?array
  {
    return $this->uno('SELECT * FROM users WHERE token_baja = ?', [$token]);
  }

  public function cambiarAceptaEmails(int $id, bool $acepta): void
  {
    $this->ejecutar('UPDATE users SET acepta_emails = ? WHERE id_user = ?', [$acepta ? 1 : 0, $id]);
  }

  public function actualizarPassword(int $id, string $hash): void
  {
    $this->ejecutar('UPDATE users SET password = ? WHERE id_user = ?', [$hash, $id]);
  }

  private function parametros(array $datos): array
  {
    return [
      'rfid' => $datos['rfid'],
      'dni' => $datos['dni'],
      'nombre' => $datos['nombre'],
      'apellido' => $datos['apellido'] ?: null,
      'nacimiento' => $datos['nacimiento'] ?: null,
      'email' => $datos['email'] ?: null,
      'telefono' => $datos['telefono'] ?: null,
      'tipo' => $datos['tipo']->value,
    ];
  }
}
