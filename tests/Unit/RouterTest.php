<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Container;
use App\Core\Request;
use App\Core\Router;
use App\Exceptions\HttpException;
use PHPUnit\Framework\TestCase;

final class ControladorDePrueba
{
  public array $llamadas = [];

  public function accion(Request $request, string ...$parametros): void
  {
    $this->llamadas[] = $parametros;
  }
}

final class RouterTest extends TestCase
{
  private Container $container;
  private Router $router;

  protected function setUp(): void
  {
    $this->container = new Container();
    $this->router = new Router($this->container);
    $this->router->get('/usuarios/buscar', [ControladorDePrueba::class, 'accion']);
    $this->router->get('/usuarios/{dni:\d+}', [ControladorDePrueba::class, 'accion']);
    $this->router->get('/clases/{id:\d+}/miembros/{usuario:\d+}', [ControladorDePrueba::class, 'accion']);
  }

  private function despachar(string $metodo, string $ruta): array
  {
    $this->router->despachar(new Request($metodo, $ruta, [], []));
    return $this->container->get(ControladorDePrueba::class)->llamadas;
  }

  public function testRutaFijaTienePrioridadSobreParametro(): void
  {
    self::assertSame([[]], $this->despachar('GET', '/usuarios/buscar'));
  }

  public function testPasaLosParametrosEnOrden(): void
  {
    self::assertSame([['7', '42']], $this->despachar('GET', '/clases/7/miembros/42'));
  }

  public function testRespetaLaRestriccionDelParametro(): void
  {
    $this->expectExceptionObject(new HttpException(404, 'Página no encontrada'));
    $this->despachar('GET', '/usuarios/abc');
  }

  public function testMetodoNoPermitido(): void
  {
    try {
      $this->despachar('DELETE', '/usuarios/12345678');
      self::fail('Debía lanzar HttpException');
    } catch (HttpException $e) {
      self::assertSame(405, $e->status);
    }
  }

  public function testPostSinTokenCsrfEsRechazado(): void
  {
    $this->router->post('/algo', [ControladorDePrueba::class, 'accion']);
    $_SESSION = [];

    try {
      $this->despachar('POST', '/algo');
      self::fail('Debía lanzar HttpException');
    } catch (HttpException $e) {
      self::assertSame(403, $e->status);
    }
  }
}
