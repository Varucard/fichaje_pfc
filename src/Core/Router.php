<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\HttpException;

/**
 * Router simple. Soporta parámetros {nombre} y {nombre:regex}, y middlewares por ruta.
 *
 * Middlewares disponibles:
 *  - auth:    exige sesión iniciada.
 *  - invitado: solo sin sesión (pantalla de login).
 * Toda ruta POST valida además el token CSRF.
 */
final class Router
{
  /** @var array<int, array{metodo: string, patron: string, accion: array, middleware: array}> */
  private array $rutas = [];

  public function __construct(private readonly Container $container)
  {
  }

  public function get(string $patron, array $accion, array $middleware = []): void
  {
    $this->agregar('GET', $patron, $accion, $middleware);
  }

  public function post(string $patron, array $accion, array $middleware = []): void
  {
    $this->agregar('POST', $patron, $accion, $middleware);
  }

  /** Agrupa rutas que comparten middlewares. */
  public function grupo(array $middleware, callable $definir): void
  {
    $grupo = new self($this->container);
    $definir($grupo);
    foreach ($grupo->rutas as $ruta) {
      $ruta['middleware'] = array_merge($middleware, $ruta['middleware']);
      $this->rutas[] = $ruta;
    }
  }

  public function despachar(Request $request): void
  {
    $metodoPermitido = false;

    foreach ($this->rutas as $ruta) {
      if (!preg_match($this->aRegex($ruta['patron']), $request->ruta(), $coincidencias)) {
        continue;
      }
      $metodoPermitido = true;
      if ($ruta['metodo'] !== $request->metodo()) {
        continue;
      }

      $this->aplicarMiddleware($ruta['middleware'], $request);

      $parametros = array_filter($coincidencias, 'is_string', ARRAY_FILTER_USE_KEY);
      [$clase, $metodo] = $ruta['accion'];
      $controlador = $this->container->get($clase);
      $controlador->$metodo($request, ...array_values($parametros));
      return;
    }

    throw $metodoPermitido
      ? new HttpException(405, 'Método no permitido')
      : new HttpException(404, 'Página no encontrada');
  }

  private function agregar(string $metodo, string $patron, array $accion, array $middleware): void
  {
    $this->rutas[] = compact('metodo', 'patron', 'accion', 'middleware');
  }

  private function aRegex(string $patron): string
  {
    $regex = preg_replace_callback(
      // Admite cuantificadores con llaves dentro del regex del parámetro: {token:[0-9a-f]{32}}
      '#\{(\w+)(?::((?:[^{}]|\{[^{}]*\})+))?\}#',
      fn (array $m) => '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')',
      $patron
    );
    return '#^' . $regex . '$#u';
  }

  private function aplicarMiddleware(array $middleware, Request $request): void
  {
    if ($request->metodo() === 'POST' && !Csrf::valido($request->input('_token'))) {
      throw new HttpException(403, 'La sesión expiró o el formulario es inválido. Volvé a cargar la página e intentá de nuevo.');
    }

    foreach ($middleware as $nombre) {
      switch ($nombre) {
        case 'auth':
          if (!Auth::check()) {
            if ($request->esApi()) {
              throw new HttpException(401, 'No autenticado');
            }
            redirigir('/login');
          }
          break;
        case 'invitado':
          if (Auth::check()) {
            redirigir('/dashboard');
          }
          break;
        default:
          throw new \LogicException("Middleware desconocido: {$nombre}");
      }
    }
  }
}
