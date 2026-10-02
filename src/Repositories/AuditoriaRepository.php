<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class AuditoriaRepository extends Repository
{
  public function registrar(array $entrada): void
  {
    $this->ejecutar(
      'INSERT INTO auditoria (fecha, id_usuario, usuario, accion, entidad, entidad_id, descripcion, datos, ip)
        VALUES (:fecha, :id_usuario, :usuario, :accion, :entidad, :entidad_id, :descripcion, :datos, :ip)',
      $entrada
    );
  }

  /**
   * Búsqueda paginada con filtros opcionales: desde, hasta (Y-m-d), accion (prefijo), texto.
   *
   * @return array{filas: array, total: int}
   */
  public function buscar(array $filtros, int $pagina, int $porPagina): array
  {
    $condiciones = [];
    $parametros = [];

    if (!empty($filtros['desde'])) {
      $condiciones[] = 'fecha >= :desde';
      $parametros['desde'] = $filtros['desde'] . ' 00:00:00';
    }
    if (!empty($filtros['hasta'])) {
      $condiciones[] = 'fecha <= :hasta';
      $parametros['hasta'] = $filtros['hasta'] . ' 23:59:59';
    }
    if (!empty($filtros['accion'])) {
      $condiciones[] = 'accion LIKE :accion';
      $parametros['accion'] = $filtros['accion'] . '%';
    }
    if (!empty($filtros['texto'])) {
      $condiciones[] = '(descripcion LIKE :t1 OR usuario LIKE :t2 OR entidad_id = :t3)';
      $parametros['t1'] = $parametros['t2'] = '%' . $filtros['texto'] . '%';
      $parametros['t3'] = $filtros['texto'];
    }

    $where = $condiciones ? ' WHERE ' . implode(' AND ', $condiciones) : '';
    $total = (int) $this->valor('SELECT COUNT(*) FROM auditoria' . $where, $parametros);

    $stmt = $this->pdo->prepare('SELECT * FROM auditoria' . $where . ' ORDER BY fecha DESC, id DESC LIMIT :limite OFFSET :desplazamiento');
    foreach ($parametros as $clave => $valor) {
      $stmt->bindValue($clave, $valor);
    }
    $stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue('desplazamiento', ($pagina - 1) * $porPagina, PDO::PARAM_INT);
    $stmt->execute();

    return ['filas' => $stmt->fetchAll(), 'total' => $total];
  }

  /** Historial de una entidad puntual (ej: todas las acciones sobre un usuario). */
  public function deEntidad(string $entidad, string $id, int $limite = 20): array
  {
    $stmt = $this->pdo->prepare(
      'SELECT * FROM auditoria WHERE entidad = :entidad AND entidad_id = :id ORDER BY fecha DESC, id DESC LIMIT :limite'
    );
    $stmt->bindValue('entidad', $entidad);
    $stmt->bindValue('id', $id);
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }
}
