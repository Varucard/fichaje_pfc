<?php

declare(strict_types=1);

namespace App\Core;

use Closure;
use ReflectionClass;
use ReflectionNamedType;
use RuntimeException;

/**
 * Contenedor de dependencias mínimo con autowiring por tipo de constructor.
 * Cada clase se instancia una sola vez por request.
 */
final class Container
{
  /** @var array<string, Closure> */
  private array $fabricas = [];

  /** @var array<string, object> */
  private array $instancias = [];

  public function registrar(string $clase, Closure $fabrica): void
  {
    $this->fabricas[$clase] = $fabrica;
  }

  /**
   * @template T of object
   * @param class-string<T> $clase
   * @return T
   */
  public function get(string $clase): object
  {
    if (isset($this->instancias[$clase])) {
      return $this->instancias[$clase];
    }

    $instancia = isset($this->fabricas[$clase])
      ? ($this->fabricas[$clase])($this)
      : $this->construir($clase);

    return $this->instancias[$clase] = $instancia;
  }

  private function construir(string $clase): object
  {
    if (!class_exists($clase)) {
      throw new RuntimeException("No existe la clase {$clase}");
    }

    $reflexion = new ReflectionClass($clase);
    $constructor = $reflexion->getConstructor();
    if ($constructor === null) {
      return new $clase();
    }

    $argumentos = [];
    foreach ($constructor->getParameters() as $parametro) {
      $tipo = $parametro->getType();
      if ($tipo instanceof ReflectionNamedType && !$tipo->isBuiltin()) {
        $argumentos[] = $this->get($tipo->getName());
      } elseif ($parametro->isDefaultValueAvailable()) {
        $argumentos[] = $parametro->getDefaultValue();
      } else {
        throw new RuntimeException("No se puede resolver \${$parametro->getName()} de {$clase}");
      }
    }

    return $reflexion->newInstanceArgs($argumentos);
  }
}
