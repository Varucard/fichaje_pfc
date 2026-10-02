<?php

declare(strict_types=1);

use App\Core\Config;
use App\Core\Csrf;
use App\Core\Session;

/*
 * Funciones de ayuda disponibles en toda la aplicación (principalmente en las vistas).
 */

function base_path(string $ruta = ''): string
{
  return dirname(__DIR__, 2) . ($ruta !== '' ? '/' . ltrim($ruta, '/') : '');
}

/** URL absoluta dentro de la app, respetando el subdirectorio donde esté instalada. */
function url(string $ruta = '/', array $query = []): string
{
  $url = rtrim((string) Config::get('app.base_path', ''), '/') . '/' . ltrim($ruta, '/');
  return $query ? $url . '?' . http_build_query($query) : $url;
}

/** URL de un archivo estático de public/ con versión para invalidar caché. */
function asset(string $ruta): string
{
  return url($ruta) . '?v=' . rawurlencode((string) Config::get('app.version', '1'));
}

/** Escapa texto para imprimirlo en HTML. Usar SIEMPRE al mostrar datos. */
function e(mixed $valor): string
{
  return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

function csrf_token(): string
{
  return Csrf::token();
}

function csrf_field(): string
{
  return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

/** Valor anterior de un campo de formulario (después de un error de validación). */
function old(string $campo, mixed $default = ''): mixed
{
  static $viejos = null;
  $viejos ??= Session::tomarViejos();
  return $viejos[$campo] ?? $default;
}

/** Redirige a una ruta interna de la app. Rutas externas se ignoran (evita open redirect). */
function redirigir(string $ruta): never
{
  if (!str_starts_with($ruta, '/') || str_starts_with($ruta, '//')) {
    $ruta = '/dashboard';
  }
  header('Location: ' . url($ruta));
  exit;
}

function fecha(?string $valor, string $formato = 'd-m-Y'): string
{
  if ($valor === null || $valor === '') {
    return '-';
  }
  $timestamp = strtotime($valor);
  return $timestamp === false ? '-' : date($formato, $timestamp);
}

function fecha_hora(?string $valor): string
{
  return fecha($valor, 'd-m-Y H:i:s');
}

function dinero(int|float|string|null $monto): string
{
  return '$' . number_format((float) $monto, 0, ',', '.');
}
