<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

/**
 * Base de los repositorios: acá vive SOLO el acceso a datos (SQL).
 * Los errores de base de datos se propagan como excepciones y se registran en el log.
 */
abstract class Repository
{
  public function __construct(protected readonly PDO $pdo)
  {
  }

  protected function uno(string $sql, array $parametros = []): ?array
  {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($parametros);
    $fila = $stmt->fetch();
    return $fila === false ? null : $fila;
  }

  protected function todos(string $sql, array $parametros = []): array
  {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->fetchAll();
  }

  protected function valor(string $sql, array $parametros = []): mixed
  {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($parametros);
    $valor = $stmt->fetchColumn();
    return $valor === false ? null : $valor;
  }

  /** Ejecuta INSERT/UPDATE/DELETE y devuelve las filas afectadas. */
  protected function ejecutar(string $sql, array $parametros = []): int
  {
    $stmt = $this->pdo->prepare($sql);
    $stmt->execute($parametros);
    return $stmt->rowCount();
  }

  /**
   * @template T
   * @param callable(): T $operacion
   * @return T
   */
  public function transaccion(callable $operacion): mixed
  {
    if ($this->pdo->inTransaction()) {
      return $operacion();
    }

    $this->pdo->beginTransaction();
    try {
      $resultado = $operacion();
      $this->pdo->commit();
      return $resultado;
    } catch (\Throwable $e) {
      $this->pdo->rollBack();
      throw $e;
    }
  }
}
