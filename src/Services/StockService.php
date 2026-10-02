<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Auth;
use App\Exceptions\ValidacionException;
use App\Repositories\StockRepository;
use App\Repositories\UsuarioRepository;
use DateTimeImmutable;

/**
 * Stock de productos y sus movimientos (entradas, ventas y ajustes de inventario).
 * Cada movimiento bloquea el producto dentro de una transacción, así dos ventas
 * simultáneas no pueden dejar el stock negativo.
 */
final class StockService
{
  public const TIPOS = ['entrada' => 'Entrada', 'venta' => 'Venta', 'ajuste' => 'Ajuste'];

  public function __construct(
    private readonly StockRepository $stock,
    private readonly UsuarioRepository $usuarios,
    private readonly AuditoriaService $auditoria,
  ) {
  }

  /** Stock resultante de un movimiento. Falla si quedaría negativo. */
  public static function nuevoStock(int $actual, int $cantidad): int
  {
    $resultado = $actual + $cantidad;
    if ($resultado < 0) {
      throw new ValidacionException("Stock insuficiente: hay {$actual} unidad(es) y se quieren descontar " . abs($cantidad) . '.');
    }
    return $resultado;
  }

  public static function bajoMinimo(array $producto): bool
  {
    return (int) $producto['activo'] && (int) $producto['stock'] <= (int) $producto['stock_minimo'];
  }

  public function resumen(): array
  {
    $hoy = new DateTimeImmutable('today');
    return [
      'productos' => $this->stock->listar(),
      'bajo_minimo' => $this->stock->cantidadBajoMinimo(),
      'ventas_mes' => $this->stock->ventasEntre($hoy->modify('first day of this month'), $hoy),
    ];
  }

  public function cantidadBajoMinimo(): int
  {
    return $this->stock->cantidadBajoMinimo();
  }

  public function detalle(int $id): array
  {
    $producto = $this->stock->buscarPorId($id) ?? throw new ValidacionException('El producto no existe.');
    return ['producto' => $producto, 'movimientos' => $this->stock->movimientosDe($id)];
  }

  public function crear(array $entrada): int
  {
    $datos = $this->normalizar($entrada);
    $stockInicial = $this->entero($entrada['stock_inicial'] ?? '0', 'El stock inicial');

    if ($this->stock->buscarPorNombre($datos['nombre'])) {
      throw new ValidacionException("Ya existe un producto llamado \"{$datos['nombre']}\".");
    }

    return $this->stock->transaccion(function () use ($datos, $stockInicial): int {
      $id = $this->stock->crear($datos);
      $this->auditoria->registrar('stock.producto_alta', "Alta del producto {$datos['nombre']} (" . dinero($datos['precio']) . ')', 'producto', $id);
      if ($stockInicial > 0) {
        $this->movimiento($id, 'entrada', $stockInicial, null, null, 'Stock inicial');
      }
      return $id;
    });
  }

  public function actualizar(int $id, array $entrada): void
  {
    $anterior = $this->stock->buscarPorId($id) ?? throw new ValidacionException('El producto no existe.');
    $datos = $this->normalizar($entrada);

    $otro = $this->stock->buscarPorNombre($datos['nombre']);
    if ($otro && (int) $otro['id'] !== $id) {
      throw new ValidacionException("Ya existe otro producto llamado \"{$datos['nombre']}\".");
    }

    $this->stock->actualizar($id, $datos);
    $cambios = AuditoriaService::cambios($anterior, $datos, ['nombre', 'descripcion', 'precio', 'stock_minimo']);
    if ($cambios) {
      $this->auditoria->registrar('stock.producto_actualizacion', "Actualización del producto {$datos['nombre']}", 'producto', $id, $cambios);
    }
  }

  public function cambiarActivo(int $id, bool $activo): array
  {
    $producto = $this->stock->buscarPorId($id) ?? throw new ValidacionException('El producto no existe.');
    $this->stock->cambiarActivo($id, $activo);
    $this->auditoria->registrar(
      $activo ? 'stock.producto_activacion' : 'stock.producto_desactivacion',
      ($activo ? 'Reactivación' : 'Desactivación') . " del producto {$producto['nombre']}",
      'producto',
      $id,
    );
    return $producto;
  }

  /**
   * Registra un movimiento a partir del formulario.
   * - entrada: cantidad > 0, costo unitario opcional.
   * - venta:   cantidad > 0, precio (por defecto el del producto), DNI de cliente opcional.
   * - ajuste:  stock real contado y motivo obligatorio.
   */
  public function registrarMovimiento(int $id, string $tipo, array $entrada): array
  {
    $observacion = trim((string) ($entrada['observacion'] ?? '')) ?: null;

    return match ($tipo) {
      'entrada' => $this->movimiento(
        $id,
        'entrada',
        $this->positivo($entrada['cantidad'] ?? ''),
        $this->monto($entrada['precio'] ?? '', opcional: true),
        null,
        $observacion
      ),
      'venta' => $this->movimiento(
        $id,
        'venta',
        -$this->positivo($entrada['cantidad'] ?? ''),
        $this->monto($entrada['precio'] ?? '', opcional: true),
        $this->cliente($entrada['dni'] ?? ''),
        $observacion
      ),
      'ajuste' => $this->ajuste($id, $this->entero($entrada['stock_real'] ?? '', 'El stock real'), $observacion),
      default => throw new ValidacionException('Tipo de movimiento no válido.'),
    };
  }

  private function ajuste(int $id, int $stockReal, ?string $motivo): array
  {
    if ($motivo === null) {
      throw new ValidacionException('Indicá el motivo del ajuste (ej: inventario, rotura, vencimiento).');
    }
    return $this->stock->transaccion(function () use ($id, $stockReal, $motivo): array {
      $producto = $this->stock->buscarParaActualizar($id) ?? throw new ValidacionException('El producto no existe.');
      $diferencia = $stockReal - (int) $producto['stock'];
      if ($diferencia === 0) {
        throw new ValidacionException('El stock real coincide con el registrado: no hay nada que ajustar.');
      }
      return $this->movimiento($id, 'ajuste', $diferencia, null, null, $motivo);
    });
  }

  /** @return array{producto: array, cantidad: int, stock: int, total: ?float} */
  private function movimiento(int $id, string $tipo, int $cantidad, ?float $precio, ?array $cliente, ?string $observacion): array
  {
    return $this->stock->transaccion(function () use ($id, $tipo, $cantidad, $precio, $cliente, $observacion): array {
      $producto = $this->stock->buscarParaActualizar($id) ?? throw new ValidacionException('El producto no existe.');
      if ($tipo === 'venta' && !(int) $producto['activo']) {
        throw new ValidacionException('El producto está desactivado.');
      }

      $stock = self::nuevoStock((int) $producto['stock'], $cantidad);
      if ($tipo === 'venta') {
        $precio ??= (float) $producto['precio'];
      }
      $total = $precio !== null ? round($precio * abs($cantidad), 2) : null;

      $this->stock->fijarStock($id, $stock);
      $this->stock->registrarMovimiento([
        'id_producto' => $id,
        'tipo' => $tipo,
        'cantidad' => $cantidad,
        'stock_resultante' => $stock,
        'precio_unitario' => $precio,
        'total' => $total,
        'id_cliente' => $cliente['id_user'] ?? null,
        'observacion' => $observacion,
        'id_admin' => Auth::usuario()['id'] ?? null,
      ]);

      $descripcion = match ($tipo) {
        'entrada' => "Entrada de {$cantidad} {$producto['nombre']}",
        'venta' => 'Venta de ' . abs($cantidad) . " {$producto['nombre']} por " . dinero($total)
          . ($cliente ? " a {$cliente['user_name']} {$cliente['user_surname']}" : ''),
        'ajuste' => "Ajuste de {$producto['nombre']}: " . ($cantidad > 0 ? '+' : '') . $cantidad . " ({$observacion})",
      };
      $this->auditoria->registrar("stock.{$tipo}", $descripcion . " — stock: {$stock}", 'producto', $id);

      return ['producto' => $producto, 'cantidad' => $cantidad, 'stock' => $stock, 'total' => $total];
    });
  }

  private function cliente(string $dni): ?array
  {
    $dni = preg_replace('/\D/', '', $dni);
    if ($dni === '') {
      return null;
    }
    return $this->usuarios->buscarPorDni($dni) ?? throw new ValidacionException("No existe un cliente con el DNI {$dni}.");
  }

  private function normalizar(array $entrada): array
  {
    $nombre = preg_replace('/\s+/u', ' ', trim((string) ($entrada['nombre'] ?? '')));
    if ($nombre === '') {
      throw new ValidacionException('Ingresá el nombre del producto.');
    }
    return [
      'nombre' => mb_substr($nombre, 0, 120),
      'descripcion' => mb_substr(trim((string) ($entrada['descripcion'] ?? '')), 0, 255) ?: null,
      'precio' => $this->monto($entrada['precio'] ?? '') ?? 0.0,
      'stock_minimo' => $this->entero($entrada['stock_minimo'] ?? '0', 'El stock mínimo'),
    ];
  }

  private function monto(mixed $valor, bool $opcional = false): ?float
  {
    $valor = str_replace(['$', ' '], '', (string) $valor);
    if ($valor === '') {
      if ($opcional) {
        return null;
      }
      throw new ValidacionException('Ingresá el precio.');
    }
    if (str_contains($valor, ',')) {
      $valor = str_replace(['.', ','], ['', '.'], $valor);
    }
    if (!is_numeric($valor) || (float) $valor < 0) {
      throw new ValidacionException('El precio no es válido.');
    }
    return round((float) $valor, 2);
  }

  private function entero(mixed $valor, string $campo): int
  {
    $valor = trim((string) $valor);
    if ($valor === '' || !ctype_digit($valor)) {
      throw new ValidacionException("{$campo} debe ser un número entero mayor o igual a 0.");
    }
    return (int) $valor;
  }

  private function positivo(mixed $valor): int
  {
    $cantidad = $this->entero($valor, 'La cantidad');
    if ($cantidad === 0) {
      throw new ValidacionException('La cantidad debe ser mayor a 0.');
    }
    return $cantidad;
  }
}
