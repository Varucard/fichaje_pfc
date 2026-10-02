<?php

declare(strict_types=1);

use App\Core\Env;

/*
 * Configuración de la aplicación. Los valores salen del archivo .env (ver .env.example).
 */

$urlApp = rtrim((string) Env::get('APP_URL', Env::get('URLROOT', '')), '/');

// Subdirectorio donde vive la app. Si no se define APP_URL se deduce del script ejecutado.
$basePath = $urlApp !== ''
  ? rtrim((string) parse_url($urlApp, PHP_URL_PATH), '/')
  : rtrim(preg_replace('#/public$#', '', str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''))) ?? '', '/.');

return [
  'app' => [
    'nombre' => Env::get('SITENAME', 'Palillo Fight Club'),
    'version' => Env::get('APPVERSION', '3.1.1'),
    'url' => $urlApp,
    'base_path' => $basePath,
    'debug' => Env::bool('APP_DEBUG'),
    'zona_horaria' => Env::get('APP_TIMEZONE', 'America/Argentina/Buenos_Aires'),
  ],

  'db' => [
    'host' => Env::get('MYSQL_DB_HOST', '127.0.0.1'),
    'puerto' => (int) Env::get('MYSQL_DB_PORT', '3306'),
    'nombre' => Env::get('MYSQL_DB_NAME', 'pfc'),
    'usuario' => Env::get('MYSQL_DB_USER', 'root'),
    'password' => Env::get('MYSQL_DB_PASSWORD', ''),
  ],

  'arduino' => [
    'ip' => Env::get('IP_ARDUINO', ''),
    'puerto' => (int) Env::get('ARDUINO_PORT', '8080'),
    // Token que el lector envía en cada lectura (?auth=...). Debe coincidir con firmware/pfc/config.h
    'token' => Env::get('ARDUINO_TOKEN', ''),
  ],

  'fichajes' => [
    // Si el mismo alumno pasa el llavero de nuevo dentro de estos minutos no se registra otra fichada.
    'minutos_entre_fichadas' => (int) Env::get('MINUTOS_ENTRE_FICHADAS', '5'),
  ],

  'liquidaciones' => [
    // % de lo cobrado en sus clases que se liquida a un profesor sin porcentaje propio.
    'porcentaje_defecto' => (float) Env::get('LIQUIDACION_PORCENTAJE', '50'),
  ],

  'pagos' => [
    // Días antes/después del vencimiento en los que se marca la cuota en rojo.
    'dias_aviso_vencimiento' => (int) Env::get('DIAS_AVISO_VENCIMIENTO', '5'),
  ],

  'log' => [
    // Nivel mínimo a registrar: debug | info | warning | error
    'nivel' => Env::get('LOG_NIVEL', 'info'),
    // Los archivos de log más viejos que esta cantidad de días se borran (0 = nunca)
    'dias_retencion' => (int) Env::get('LOG_DIAS_RETENCION', '90'),
  ],

  'backup_dir' => Env::get('BACKUP_DIR', base_path('storage/backups')),
];
