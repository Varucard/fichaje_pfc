<?php

declare(strict_types=1);

/*
 * Genera los avisos del día (vencimiento, deuda, inactividad, cumpleaños, resumen) y
 * envía la cola de emails. Ejecutarlo periódicamente (cada 15 minutos está bien):
 * nunca envía dos veces el mismo aviso.
 *
 *   Linux (cron):  0,15,30,45 * * * *  php /ruta/al/proyecto/bin/procesar-emails.php
 *   Windows:       Programador de tareas → php.exe C:\xampp\htdocs\fichaje_pfc\bin\procesar-emails.php
 *   Docker:        el servicio "tareas" del docker-compose ya lo ejecuta.
 */

use App\Core\App;
use App\Services\AvisosService;
use App\Services\EmailService;
use App\Support\Log;

if (PHP_SAPI !== 'cli') {
  exit("Este script solo se ejecuta desde la consola.\n");
}

require dirname(__DIR__) . '/vendor/autoload.php';
App::configurar();
Log::contexto(['peticion' => 'cli bin/procesar-emails.php']);

try {
  $encolados = App::container()->get(AvisosService::class)->generar();
  $resultado = App::container()->get(EmailService::class)->procesarCola(100);

  printf(
    "[%s] Avisos nuevos: %s | Enviados: %d | Con error: %d%s\n",
    date('Y-m-d H:i:s'),
    json_encode($encolados),
    $resultado['enviados'],
    $resultado['errores'],
    !empty($resultado['ocupado']) ? ' (otro proceso estaba enviando)' : ''
  );
} catch (Throwable $e) {
  Log::error('Falló el procesamiento de emails', $e);
  fwrite(STDERR, $e->getMessage() . "\n");
  exit(1);
}
