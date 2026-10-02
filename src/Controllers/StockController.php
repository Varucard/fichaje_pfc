<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Request;
use App\Exceptions\ValidacionException;
use App\Services\StockService;

final class StockController extends Controller
{
  private const CAMPOS_PRODUCTO = ['nombre', 'descripcion', 'precio', 'stock_minimo', 'stock_inicial'];
  private const CAMPOS_MOVIMIENTO = ['cantidad', 'precio', 'dni', 'stock_real', 'observacion'];

  public function __construct(private readonly StockService $stock)
  {
  }

  public function index(Request $request): void
  {
    $this->render('stock/lista', $this->stock->resumen() + ['titulo' => 'Stock']);
  }

  public function crear(Request $request): void
  {
    $this->render('stock/crear', ['titulo' => 'Nuevo producto']);
  }

  public function guardar(Request $request): void
  {
    $entrada = $this->campos($request, self::CAMPOS_PRODUCTO);

    try {
      $id = $this->stock->crear($entrada);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/stock/nuevo', $entrada);
    }

    $this->exito('Producto creado.', '/stock/' . $id);
  }

  public function mostrar(Request $request, string $id): void
  {
    try {
      $datos = $this->stock->detalle((int) $id);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/stock');
    }

    $this->render('stock/detalle', $datos + ['titulo' => $datos['producto']['nombre'], 'tipos' => StockService::TIPOS]);
  }

  public function actualizar(Request $request, string $id): void
  {
    try {
      $this->stock->actualizar((int) $id, $this->campos($request, self::CAMPOS_PRODUCTO));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/stock/' . $id);
    }

    $this->exito('Producto actualizado.', '/stock/' . $id);
  }

  public function cambiarActivo(Request $request, string $id): void
  {
    $activar = $request->input('activo') === '1';

    try {
      $this->stock->cambiarActivo((int) $id, $activar);
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/stock/' . $id);
    }

    $this->exito($activar ? 'Producto reactivado.' : 'Producto desactivado.', '/stock/' . $id);
  }

  public function movimiento(Request $request, string $id): void
  {
    $tipo = (string) $request->input('tipo', '');

    try {
      $resultado = $this->stock->registrarMovimiento((int) $id, $tipo, $this->campos($request, self::CAMPOS_MOVIMIENTO));
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/stock/' . $id);
    }

    $mensaje = match ($tipo) {
      'venta' => 'Venta registrada por ' . dinero($resultado['total']) . '.',
      'entrada' => 'Entrada registrada.',
      default => 'Ajuste registrado.',
    };
    $this->exito("{$mensaje} Stock actual: {$resultado['stock']}.", '/stock/' . $id);
  }

  private function campos(Request $request, array $campos): array
  {
    $datos = [];
    foreach ($campos as $campo) {
      $datos[$campo] = (string) $request->input($campo, '');
    }
    return $datos;
  }
}
