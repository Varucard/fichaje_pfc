<?php

declare(strict_types=1);

namespace App\Core;

use App\Exceptions\HttpException;
use App\Support\Log;
use PDO;
use Throwable;

/**
 * Arranque de la aplicación: configuración, sesión, dependencias, rutas y manejo de errores.
 */
final class App
{
  private static ?Container $container = null;

  public static function container(): Container
  {
    return self::$container ??= new Container();
  }

  /** Carga entorno y configuración. Lo usan tanto la web como los scripts de bin/. */
  public static function configurar(): void
  {
    Env::cargar(base_path('.env'));
    Config::cargar(require base_path('config/app.php'));
    date_default_timezone_set((string) Config::get('app.zona_horaria'));

    $container = self::container();
    $container->registrar(PDO::class, fn () => Database::conectar(Config::get('db')));
    $container->registrar(View::class, fn () => new View(base_path('views')));
  }

  public static function ejecutar(): void
  {
    self::configurar();
    self::encabezadosDeSeguridad();
    Session::iniciar();

    $request = Request::desdeGlobales((string) Config::get('app.base_path'));
    Log::contexto([
      'ip' => $_SERVER['REMOTE_ADDR'] ?? 'cli',
      'peticion' => $request->metodo() . ' ' . $request->ruta(),
      'usuario' => Auth::usuario()['id'] ?? null,
    ]);

    $router = new Router(self::container());
    (require base_path('config/routes.php'))($router);

    try {
      $router->despachar($request);
    } catch (Throwable $e) {
      self::manejarError($e, $request);
    }
  }

  /**
   * CSP: solo scripts propios (sin inline), estilos propios + Font Awesome (cdnjs).
   * Aunque apareciera un XSS, no podría cargar ni ejecutar scripts externos.
   */
  private static function encabezadosDeSeguridad(): void
  {
    if (headers_sent()) {
      return;
    }
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline' https://cdnjs.cloudflare.com; "
      . "font-src 'self' data: https://cdnjs.cloudflare.com; img-src 'self' data:; connect-src 'self'; "
      . "frame-ancestors 'none'; form-action 'self'; base-uri 'self'; object-src 'none'");
    header('X-Content-Type-Options: nosniff');
    header('Referrer-Policy: same-origin');
    header('X-Frame-Options: DENY');
  }

  private static function manejarError(Throwable $e, Request $request): void
  {
    // Clave duplicada (ej. doble clic al matricular o registrar): no es un error del sistema.
    if ($e instanceof \PDOException && $e->getCode() === '23000' && str_contains($e->getMessage(), 'Duplicate')) {
      $e = new HttpException(409, 'Ese registro ya existe (¿se envió el formulario dos veces?). Volvé atrás y revisá.');
    }
    $status = $e instanceof HttpException ? $e->status : 500;
    $mensaje = $e instanceof HttpException ? $e->getMessage() : 'Ocurrió un error inesperado.';

    if ($status === 500) {
      Log::error('Error no controlado', $e);
      if (Config::get('app.debug')) {
        $mensaje = $e::class . ': ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')';
      }
    } elseif (in_array($status, [401, 403], true)) {
      Log::warning("Acceso rechazado ({$status}): {$mensaje}");
    } else {
      Log::info("Respuesta {$status}: {$mensaje}");
    }

    if (headers_sent() === false) {
      http_response_code($status);
    }

    if ($request->esApi()) {
      header('Content-Type: application/json; charset=utf-8');
      echo json_encode(['status' => 'error', 'message' => $mensaje], JSON_UNESCAPED_UNICODE);
      return;
    }

    echo self::container()->get(View::class)->render(
      'errors/error',
      ['status' => $status, 'mensaje' => $mensaje, 'titulo' => "Error {$status}"],
      'layouts/simple'
    );
  }
}
