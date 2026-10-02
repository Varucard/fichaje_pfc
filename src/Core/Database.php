<?php

declare(strict_types=1);

namespace App\Core;

use PDO;

/**
 * Crea la única conexión PDO de la aplicación (antes cada modelo abría la suya).
 */
final class Database
{
  public static function conectar(array $config): PDO
  {
    $dsn = sprintf(
      'mysql:host=%s;port=%d;dbname=%s;charset=utf8mb4',
      $config['host'],
      (int) $config['puerto'],
      $config['nombre']
    );

    $pdo = new PDO($dsn, $config['usuario'], $config['password'], [
      PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
      PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
      PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    // NOW() y CURDATE() de MySQL en la misma zona horaria que PHP (APP_TIMEZONE).
    $pdo->exec("SET time_zone = '" . (new \DateTimeImmutable())->format('P') . "'");
    return $pdo;
  }
}
