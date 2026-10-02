<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\App;
use App\Core\Session;
use App\Core\View;

/**
 * Base de los controladores. Un controlador solo:
 *  1. lee la request,
 *  2. llama a un servicio,
 *  3. devuelve una vista, JSON o una redirección con mensaje.
 */
abstract class Controller
{
  protected function render(string $vista, array $datos = [], ?string $layout = 'layouts/main'): void
  {
    echo App::container()->get(View::class)->render($vista, $datos, $layout);
  }

  protected function json(mixed $datos, int $status = 200): void
  {
    $cuerpo = json_encode($datos, JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR);
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    header('Content-Length: ' . strlen($cuerpo));
    echo $cuerpo;
  }

  protected function exito(string $mensaje, string $ruta): never
  {
    Session::flash('exito', $mensaje);
    redirigir($ruta);
  }

  protected function error(string $mensaje, string $ruta, array $datosFormulario = []): never
  {
    Session::flash('error', $mensaje);
    if ($datosFormulario) {
      Session::guardarViejos($datosFormulario);
    }
    redirigir($ruta);
  }
}
