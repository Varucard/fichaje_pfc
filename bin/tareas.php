<?php

declare(strict_types=1);

/*
 * Tareas programadas: avisos y envío de emails, backup diario de la base y control
 * del lector. Ejecutarlo cada 15 minutos (no repite envíos ni backups):
 *
 *   Linux (cron):  0,15,30,45 * * * *  php /ruta/al/proyecto/bin/tareas.php
 *   Windows:       Programador de tareas → php.exe C:\xampp\htdocs\fichaje_pfc\bin\tareas.php
 *   Docker:        el servicio "tareas" del docker-compose ya lo ejecuta.
 */

use App\Core\App;
use App\Services\TareasService;
use App\Support\Log;

if (PHP_SAPI !== 'cli') {
  exit("Este script solo se ejecuta desde la consola.\n");
}

require dirname(__DIR__) . '/vendor/autoload.php';
App::configurar();
Log::contexto(['peticion' => 'cli bin/tareas.php']);

$resumen = App::container()->get(TareasService::class)->ejecutar();
printf("[%s] %s\n", date('Y-m-d H:i:s'), json_encode($resumen, JSON_UNESCAPED_UNICODE));
