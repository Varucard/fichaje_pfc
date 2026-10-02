<?php

declare(strict_types=1);

namespace App\Services;

use App\Domain\PlanDePago;
use App\Exceptions\ValidacionException;
use App\Repositories\PromocionRepository;

/**
 * Promociones de pago: "N meses pagos + M bonificados" con descuento opcional.
 * No se editan ni se borran (quedan asociadas a los pagos); se desactivan.
 */
final class PromocionService
{
  public function __construct(
    private readonly PromocionRepository $promociones,
    private readonly AuditoriaService $auditoria,
  ) {
  }

  public function listar(): array
  {
    return $this->promociones->listar();
  }

  /** Promociones activas como planes de pago (para el formulario de cobro). */
  public function planesActivos(): array
  {
    return array_map(fn (array $fila) => PlanDePago::deFila($fila), $this->promociones->listar(soloActivas: true));
  }

  /** "meses:3" o "promo:5" (valor del selector de plan) => PlanDePago. */
  public function planDesde(?string $valor): PlanDePago
  {
    $valor = (string) $valor;
    if ($valor === '' || $valor === 'meses:1') {
      return PlanDePago::meses(1);
    }
    if (preg_match('/^meses:(\d{1,2})$/', $valor, $m)) {
      return PlanDePago::meses((int) $m[1]);
    }
    if (preg_match('/^promo:(\d+)$/', $valor, $m)) {
      $promocion = $this->promociones->buscarPorId((int) $m[1]);
      if (!$promocion || !(int) $promocion['activa']) {
        throw new ValidacionException('La promoción no existe o no está activa.');
      }
      return PlanDePago::deFila($promocion);
    }
    throw new ValidacionException('El plan de pago no es válido.');
  }

  public function crear(array $entrada): void
  {
    $nombre = preg_replace('/\s+/u', ' ', trim((string) ($entrada['nombre'] ?? '')));
    if ($nombre === '') {
      throw new ValidacionException('Ingresá el nombre de la promoción.');
    }
    if ($this->promociones->buscarPorNombre($nombre)) {
      throw new ValidacionException("Ya existe una promoción llamada \"{$nombre}\".");
    }

    $datos = [
      'nombre' => mb_substr($nombre, 0, 80),
      'meses_pagos' => self::entero($entrada['meses_pagos'] ?? '', 1, PlanDePago::MAX_MESES, 'Los meses pagos'),
      'meses_bonificados' => self::entero($entrada['meses_bonificados'] ?? '0', 0, PlanDePago::MAX_MESES, 'Los meses bonificados'),
      'descuento' => self::numero($entrada['descuento'] ?? '0'),
    ];
    // Valida la combinación (descuento < 100, etc.).
    $plan = PlanDePago::deFila($datos + ['id' => 0]);
    if ($datos['meses_bonificados'] === 0 && $datos['descuento'] <= 0) {
      throw new ValidacionException('La promoción debe tener meses bonificados o un descuento.');
    }

    $id = $this->promociones->crear($datos);
    $this->auditoria->registrar('pago.promocion_alta', 'Alta de la promoción ' . PlanDePago::deFila($datos + ['id' => $id])->descripcion(), 'promocion', $id);
  }

  public function cambiarActiva(int $id, bool $activa): void
  {
    $promocion = $this->promociones->buscarPorId($id) ?? throw new ValidacionException('La promoción no existe.');
    $this->promociones->cambiarActiva($id, $activa);
    $this->auditoria->registrar(
      'pago.promocion_' . ($activa ? 'activacion' : 'desactivacion'),
      ($activa ? 'Activación' : 'Desactivación') . " de la promoción {$promocion['nombre']}",
      'promocion',
      $id,
    );
  }

  private static function entero(mixed $valor, int $min, int $max, string $campo): int
  {
    $valor = trim((string) $valor);
    if (!ctype_digit($valor) || (int) $valor < $min || (int) $valor > $max) {
      throw new ValidacionException("{$campo} deben ser un número entre {$min} y {$max}.");
    }
    return (int) $valor;
  }

  private static function numero(mixed $valor): float
  {
    $valor = str_replace([',', '%', ' '], ['.', '', ''], trim((string) $valor));
    if ($valor === '') {
      return 0.0;
    }
    if (!is_numeric($valor)) {
      throw new ValidacionException('El descuento no es válido.');
    }
    return round((float) $valor, 2);
  }
}
