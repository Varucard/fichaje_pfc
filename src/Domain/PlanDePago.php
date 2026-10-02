<?php

declare(strict_types=1);

namespace App\Domain;

use App\Exceptions\ValidacionException;

/**
 * Qué cubre un pago: N meses a precio de cuota, o una promoción
 * ("3 meses + 1 bonificado", "6 meses con 10 % off").
 */
final class PlanDePago
{
  public const MAX_MESES = 12;

  private function __construct(
    public readonly int $mesesPagos,
    public readonly int $mesesBonificados,
    public readonly float $descuento,
    public readonly ?int $idPromocion,
    public readonly string $nombre,
  ) {
    if ($mesesPagos < 1 || $mesesPagos + $mesesBonificados > self::MAX_MESES * 2) {
      throw new ValidacionException('La cantidad de meses no es válida.');
    }
    if ($descuento < 0 || $descuento >= 100) {
      throw new ValidacionException('El descuento debe ser mayor o igual a 0 y menor a 100.');
    }
  }

  public static function meses(int $meses): self
  {
    if ($meses < 1 || $meses > self::MAX_MESES) {
      throw new ValidacionException('Se pueden pagar entre 1 y ' . self::MAX_MESES . ' meses.');
    }
    return new self($meses, 0, 0.0, null, $meses === 1 ? '1 mes' : "{$meses} meses");
  }

  public static function deFila(array $promocion): self
  {
    return new self(
      (int) $promocion['meses_pagos'],
      (int) $promocion['meses_bonificados'],
      (float) $promocion['descuento'],
      (int) $promocion['id'],
      (string) $promocion['nombre'],
    );
  }

  /** Meses de vigencia que suma el pago (cobrados + bonificados). */
  public function mesesCubiertos(): int
  {
    return $this->mesesPagos + $this->mesesBonificados;
  }

  /** Precio del plan para una cuota mensual dada. */
  public function precio(float $cuota): float
  {
    return round($cuota * $this->mesesPagos * (1 - $this->descuento / 100), 2);
  }

  public function descripcion(): string
  {
    if ($this->idPromocion === null) {
      return $this->nombre;
    }
    $partes = ["{$this->mesesPagos} mes" . ($this->mesesPagos > 1 ? 'es' : '')];
    if ($this->mesesBonificados > 0) {
      $partes[] = "+ {$this->mesesBonificados} bonificado" . ($this->mesesBonificados > 1 ? 's' : '');
    }
    if ($this->descuento > 0) {
      $partes[] = '(' . ($this->descuento + 0) . ' % off)';
    }
    return "{$this->nombre}: " . implode(' ', $partes);
  }
}
