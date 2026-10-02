<?php

declare(strict_types=1);

/*
 * Aplica las migraciones pendientes de database/migrations.
 *
 * Uso:  php bin/migrar.php            Aplica las pendientes
 *       php bin/migrar.php --estado   Solo lista las pendientes
 *       (Docker: docker compose exec php php bin/migrar.php)
 */

use App\Core\App;
use App\Core\Migrador;

if (PHP_SAPI !== 'cli') {
  exit("Este script solo se ejecuta desde la consola.\n");
}

require dirname(__DIR__) . '/vendor/autoload.php';
App::configurar();

$migrador = new Migrador(App::container()->get(PDO::class), base_path('database/migrations'));
$pendientes = $migrador->pendientes();

if (in_array('--estado', $argv, true)) {
  echo $pendientes ? "Pendientes:\n  - " . implode("\n  - ", $pendientes) . "\n" : "La base está al día.\n";
  exit(0);
}

if (!$pendientes) {
  echo "La base está al día.\n";
  exit(0);
}

try {
  $total = $migrador->migrar(fn (string $nombre) => print("  ✔ {$nombre}\n"));
  echo "Migraciones aplicadas: {$total}\n";
} catch (RuntimeException $e) {
  fwrite(STDERR, "\n✘ " . $e->getMessage() . "\n");
  exit(1);
}
