<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
  public function __construct(
    private readonly string $metodo,
    private readonly string $ruta,
    private readonly array $query,
    private readonly array $post,
  ) {
  }

  public static function desdeGlobales(string $basePath): self
  {
    $ruta = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $ruta = rawurldecode($ruta);

    // Quita el subdirectorio donde vive la app (ej: /fichaje_pfc o /fichaje_pfc/public)
    foreach ([$basePath . '/public', $basePath] as $prefijo) {
      if ($prefijo !== '' && ($ruta === $prefijo || str_starts_with($ruta, $prefijo . '/'))) {
        $ruta = substr($ruta, strlen($prefijo));
        break;
      }
    }

    $ruta = '/' . trim($ruta, '/');

    return new self(strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'), $ruta, $_GET, $_POST);
  }

  public function metodo(): string
  {
    return $this->metodo;
  }

  public function ruta(): string
  {
    return $this->ruta;
  }

  public function query(string $clave, ?string $default = null): ?string
  {
    $valor = $this->query[$clave] ?? $default;
    return is_string($valor) ? trim($valor) : $default;
  }

  public function input(string $clave, ?string $default = null): ?string
  {
    $valor = $this->post[$clave] ?? $default;
    return is_string($valor) ? trim($valor) : $default;
  }

  /** @return array<int, string> */
  public function inputArray(string $clave): array
  {
    $valor = $this->post[$clave] ?? [];
    return is_array($valor) ? array_values(array_filter($valor, 'is_string')) : [];
  }

  public function tiene(string $clave): bool
  {
    return isset($this->post[$clave]) && $this->post[$clave] !== '';
  }

  public function esApi(): bool
  {
    return str_starts_with($this->ruta, '/api/') || $this->ruta === '/config/get_uid.php';
  }
}
