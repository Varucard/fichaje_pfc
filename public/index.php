<?php

declare(strict_types=1);

/*
 * Front controller: todas las peticiones entran por acá.
 */

// Servidor embebido de PHP (composer serve): servir archivos estáticos directamente.
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
  return false;
}

$autoload = dirname(__DIR__) . '/vendor/autoload.php';
if (!is_file($autoload)) {
  http_response_code(500);
  exit('Faltan las dependencias. Ejecutá "composer install" en la raíz del proyecto.');
}
require $autoload;

App\Core\App::ejecutar();
