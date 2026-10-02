<?php

declare(strict_types=1);

namespace App\Core;

use RuntimeException;

/**
 * Renderiza plantillas PHP dentro de un layout.
 * Las vistas solo reciben datos ya preparados: no consultan la base ni aplican reglas de negocio.
 */
final class View
{
  /** @var array<int, string> Scripts de public/js que pidió la vista actual. */
  private array $scripts = [];

  public function __construct(private readonly string $directorio)
  {
  }

  /** Desde una vista: $this->script('fichajes-en-vivo') para incluir public/js/fichajes-en-vivo.js */
  public function script(string $nombre): void
  {
    $this->scripts[$nombre] = $nombre;
  }

  /** @return array<int, string> */
  public function scripts(): array
  {
    return array_values($this->scripts);
  }

  public function render(string $vista, array $datos = [], ?string $layout = 'layouts/main'): string
  {
    $contenido = $this->renderParcial($vista, $datos);

    if ($layout === null) {
      return $contenido;
    }

    return $this->renderParcial($layout, $datos + ['contenido' => $contenido]);
  }

  public function renderParcial(string $vista, array $datos = []): string
  {
    $archivo = $this->directorio . '/' . $vista . '.php';
    if (!is_file($archivo)) {
      throw new RuntimeException("No existe la vista {$vista}");
    }

    extract($datos, EXTR_SKIP);
    ob_start();
    try {
      require $archivo;
    } catch (\Throwable $e) {
      ob_end_clean();
      throw $e;
    }
    return (string) ob_get_clean();
  }
}
