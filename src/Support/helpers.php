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

/**
 * URL absoluta (con dominio) para usar fuera del navegador, por ejemplo en los emails.
 * Requiere APP_URL en el .env.
 */
function url_absoluta(string $ruta = '/', array $query = []): string
{
  $base = rtrim((string) Config::get('app.url', ''), '/');
  $url = $base . '/' . ltrim($ruta, '/');
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

/**
 * ¿Es una ruta interna de la app? Debe empezar con una sola "/" y no tener barras
 * invertidas ni caracteres de control (los navegadores tratan "/\\sitio" como "//sitio").
 */
function es_ruta_interna(string $ruta): bool
{
  return (bool) preg_match('#^/(?![/\\\\])[^\\\\\x00-\x1f\x7f]*$#', $ruta);
}

/** Redirige a una ruta interna de la app. Rutas externas se ignoran (evita open redirect). */
function redirigir(string $ruta): never
{
  if (!es_ruta_interna($ruta)) {
    $ruta = '/dashboard';
  }
  header('Location: ' . url($ruta));
  exit;
}

/** Fecha AAAA-MM-DD estricta: rechaza fechas imposibles como 2025-02-30 (que PHP "corrige"). */
function fecha_valida(?string $valor): ?DateTimeImmutable
{
  $valor = trim((string) $valor);
  $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
  return $fecha && $fecha->format('Y-m-d') === $valor ? $fecha : null;
}

/**
 * Monto escrito a mano: "12500", "12.500", "12.500,50", "12500,5" o "12500.50".
 * El punto con grupos de 3 dígitos es separador de miles; la coma, decimal.
 */
function monto_desde_texto(?string $valor): ?float
{
  $valor = str_replace(['$', ' '], '', trim((string) $valor));
  if ($valor === '') {
    return null;
  }
  if (str_contains($valor, ',')) {
    $valor = str_replace(['.', ','], ['', '.'], $valor);
  } elseif (preg_match('/^\d{1,3}(\.\d{3})+$/', $valor)) {
    $valor = str_replace('.', '', $valor);
  }
  return is_numeric($valor) ? (float) $valor : null;
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

/** $12.500 o $12.500,50 (muestra centavos solo si los hay). */
function dinero(int|float|string|null $monto): string
{
  $monto = round((float) $monto, 2);
  return ($monto < 0 ? '-$' : '$') . number_format(abs($monto), fmod($monto, 1.0) != 0.0 ? 2 : 0, ',', '.');
}
