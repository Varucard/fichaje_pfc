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
    Session::iniciar();

    $request = Request::desdeGlobales((string) Config::get('app.base_path'));
    $router = new Router(self::container());
    (require base_path('config/routes.php'))($router);

    try {
      $router->despachar($request);
    } catch (Throwable $e) {
      self::manejarError($e, $request);
    }
  }

  private static function manejarError(Throwable $e, Request $request): void
  {
    $status = $e instanceof HttpException ? $e->status : 500;
    $mensaje = $e instanceof HttpException ? $e->getMessage() : 'Ocurrió un error inesperado.';

    if ($status === 500) {
      Log::error("{$request->metodo()} {$request->ruta()}", $e);
      if (Config::get('app.debug')) {
        $mensaje = $e::class . ': ' . $e->getMessage() . ' (' . $e->getFile() . ':' . $e->getLine() . ')';
      }
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
