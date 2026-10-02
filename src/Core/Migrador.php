<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use RuntimeException;

/**
 * Aplica en orden los archivos database/migrations/NNN_nombre.sql que todavía no se
 * ejecutaron, y los registra en la tabla `migraciones`.
 *
 * Cada migración debe poder correr tanto en una base nueva (schema.sql + seed) como en
 * una base existente de producción.
 *
 * MySQL no puede revertir cambios de estructura (ALTER/CREATE), así que si una migración
 * falla a mitad de camino se guarda cuántas sentencias se aplicaron (tabla
 * migraciones_progreso) y la próxima ejecución retoma desde la que falló.
 */
final class Migrador
{
  public function __construct(
    private readonly PDO $pdo,
    private readonly string $directorio,
  ) {
  }

  /** @return array<int, string> Nombres de las migraciones pendientes. */
  public function pendientes(): array
  {
    $this->crearTablaDeControl();
    $aplicadas = $this->pdo->query('SELECT nombre FROM migraciones')->fetchAll(PDO::FETCH_COLUMN);

    $archivos = glob($this->directorio . '/*.sql') ?: [];
    sort($archivos, SORT_STRING);

    return array_values(array_diff(array_map('basename', $archivos), $aplicadas));
  }

  /**
   * Ejecuta las migraciones pendientes. Se detiene en la primera que falle.
   *
   * @param callable(string): void $avisar Recibe el nombre de cada migración aplicada.
   */
  public function migrar(callable $avisar): int
  {
    $aplicadas = 0;
    foreach ($this->pendientes() as $nombre) {
      $sentencias = self::sentencias((string) file_get_contents($this->directorio . '/' . $nombre));
      $hechas = $this->progreso($nombre);

      foreach (array_slice($sentencias, $hechas, null, true) as $indice => $sql) {
        try {
          $this->pdo->exec($sql);
        } catch (\PDOException $e) {
          throw new RuntimeException(
            sprintf(
              "Falló la migración %s en la sentencia %d de %d: %s\nSentencia: %s\n"
              . 'Corregí el problema y volvé a ejecutar: se retoma desde esta sentencia.',
              $nombre,
              $indice + 1,
              count($sentencias),
              $e->getMessage(),
              $sql
            ),
            0,
            $e
          );
        }
        $this->guardarProgreso($nombre, $indice + 1);
      }

      $stmt = $this->pdo->prepare('INSERT INTO migraciones (nombre, aplicada_en) VALUES (?, NOW())');
      $stmt->execute([$nombre]);
      $this->pdo->prepare('DELETE FROM migraciones_progreso WHERE nombre = ?')->execute([$nombre]);
      $avisar($nombre);
      $aplicadas++;
    }
    return $aplicadas;
  }

  /**
   * Divide un archivo SQL en sentencias. Ignora comentarios "--" de línea completa.
   * No soporta procedimientos almacenados (no se usan en el proyecto).
   *
   * @return array<int, string>
   */
  public static function sentencias(string $sql): array
  {
    $lineas = array_filter(
      preg_split('/\R/', $sql) ?: [],
      fn (string $linea) => !str_starts_with(ltrim($linea), '--')
    );

    return array_values(array_filter(
      array_map('trim', preg_split('/;\s*(?:\R|$)/', implode("\n", $lineas)) ?: []),
      fn (string $sentencia) => $sentencia !== ''
    ));
  }

  private function progreso(string $nombre): int
  {
    $stmt = $this->pdo->prepare('SELECT sentencias_ok FROM migraciones_progreso WHERE nombre = ?');
    $stmt->execute([$nombre]);
    return (int) $stmt->fetchColumn();
  }

  private function guardarProgreso(string $nombre, int $sentencias): void
  {
    $this->pdo->prepare('REPLACE INTO migraciones_progreso (nombre, sentencias_ok) VALUES (?, ?)')
      ->execute([$nombre, $sentencias]);
  }

  private function crearTablaDeControl(): void
  {
    $this->pdo->exec(
      'CREATE TABLE IF NOT EXISTS migraciones_progreso (
        nombre VARCHAR(190) NOT NULL PRIMARY KEY,
        sentencias_ok INT NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
    );
    $this->pdo->exec(
      'CREATE TABLE IF NOT EXISTS migraciones (
        nombre VARCHAR(190) NOT NULL PRIMARY KEY,
        aplicada_en DATETIME NOT NULL
      ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci'
    );
  }
}
