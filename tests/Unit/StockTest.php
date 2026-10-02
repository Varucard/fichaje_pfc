<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Exceptions\ValidacionException;
use App\Services\StockService;
use PHPUnit\Framework\TestCase;

final class StockTest extends TestCase
{
  public function testCalculaElStockResultante(): void
  {
    self::assertSame(15, StockService::nuevoStock(10, 5));
    self::assertSame(0, StockService::nuevoStock(3, -3));
  }

  public function testNoPermiteStockNegativo(): void
  {
    $this->expectException(ValidacionException::class);
    $this->expectExceptionMessage('Stock insuficiente');
    StockService::nuevoStock(2, -3);
  }

  public function testDetectaStockBajoSoloEnProductosActivos(): void
  {
    self::assertTrue(StockService::bajoMinimo(['activo' => 1, 'stock' => 2, 'stock_minimo' => 2]));
    self::assertFalse(StockService::bajoMinimo(['activo' => 1, 'stock' => 3, 'stock_minimo' => 2]));
    self::assertFalse(StockService::bajoMinimo(['activo' => 0, 'stock' => 0, 'stock_minimo' => 5]));
  }
}
