<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class EmailRepository extends Repository
{
  /** Encola un email. Devuelve false si ya existía uno con la misma clave_unica. */
  public function encolar(array $email): bool
  {
    return $this->ejecutar(
      'INSERT INTO emails_cola
          (tipo, id_usuario, destinatario, nombre_destinatario, asunto, cuerpo_html, cuerpo_texto, adjunto, clave_unica, creado_en, enviar_desde)
        VALUES (:tipo, :id_usuario, :destinatario, :nombre_destinatario, :asunto, :cuerpo_html, :cuerpo_texto, :adjunto, :clave_unica, NOW(), NOW())
        ON DUPLICATE KEY UPDATE id = id',
      ['asunto' => mb_substr($email['asunto'], 0, 200)] + $email
    ) === 1;
  }

  public function yaExiste(string $claveUnica): bool
  {
    return (bool) $this->valor('SELECT EXISTS(SELECT 1 FROM emails_cola WHERE clave_unica = ?)', [$claveUnica]);
  }

  public function pendientes(int $limite): array
  {
    $stmt = $this->pdo->prepare(
      "SELECT * FROM emails_cola WHERE estado = 'pendiente' AND enviar_desde <= NOW() ORDER BY id LIMIT :limite"
    );
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  public function marcarEnviado(int $id): void
  {
    $this->ejecutar("UPDATE emails_cola SET estado = 'enviado', enviado_en = NOW(), ultimo_error = NULL WHERE id = ?", [$id]);
  }

  /** Registra el error. Si se agotaron los intentos queda en 'error'; si no, se reintenta más tarde. */
  public function marcarError(int $id, string $error, int $maxIntentos): void
  {
    $this->ejecutar(
      "UPDATE emails_cola SET intentos = intentos + 1, ultimo_error = :error,
          estado = IF(intentos >= :max, 'error', 'pendiente'),
          enviar_desde = NOW() + INTERVAL (intentos * 10) MINUTE
        WHERE id = :id",
      ['error' => mb_substr($error, 0, 500), 'max' => $maxIntentos, 'id' => $id]
    );
  }

  public function reintentar(int $id): int
  {
    return $this->ejecutar(
      "UPDATE emails_cola SET estado = 'pendiente', intentos = 0, enviar_desde = NOW() WHERE id = ? AND estado IN ('error', 'cancelado')",
      [$id]
    );
  }

  public function cancelarPorClave(string $claveUnica): int
  {
    return $this->ejecutar("UPDATE emails_cola SET estado = 'cancelado' WHERE clave_unica = ? AND estado = 'pendiente'", [$claveUnica]);
  }

  public function cancelar(int $id): int
  {
    return $this->ejecutar("UPDATE emails_cola SET estado = 'cancelado' WHERE id = ? AND estado = 'pendiente'", [$id]);
  }

  public function buscarPorId(int $id): ?array
  {
    return $this->uno('SELECT * FROM emails_cola WHERE id = ?', [$id]);
  }

  /** @return array{filas: array, total: int} */
  public function listar(?string $estado, ?string $tipo, int $pagina, int $porPagina): array
  {
    $condiciones = [];
    $parametros = [];
    if ($estado) {
      $condiciones[] = 'estado = :estado';
      $parametros['estado'] = $estado;
    }
    if ($tipo) {
      $condiciones[] = 'tipo = :tipo';
      $parametros['tipo'] = $tipo;
    }
    $where = $condiciones ? ' WHERE ' . implode(' AND ', $condiciones) : '';
    $total = (int) $this->valor('SELECT COUNT(*) FROM emails_cola' . $where, $parametros);

    $stmt = $this->pdo->prepare(
      'SELECT id, tipo, id_usuario, destinatario, nombre_destinatario, asunto, adjunto, estado, intentos, ultimo_error, creado_en, enviado_en
        FROM emails_cola' . $where . ' ORDER BY id DESC LIMIT :limite OFFSET :desplazamiento'
    );
    foreach ($parametros as $clave => $valor) {
      $stmt->bindValue($clave, $valor);
    }
    $stmt->bindValue('limite', $porPagina, PDO::PARAM_INT);
    $stmt->bindValue('desplazamiento', ($pagina - 1) * $porPagina, PDO::PARAM_INT);
    $stmt->execute();
    return ['filas' => $stmt->fetchAll(), 'total' => $total];
  }

  /** @return array<string, int> estado => cantidad */
  public function contarPorEstado(): array
  {
    return array_map('intval', $this->pdo->query('SELECT estado, COUNT(*) FROM emails_cola GROUP BY estado')->fetchAll(PDO::FETCH_KEY_PAIR));
  }

  public function deUsuario(int $idUsuario, int $limite = 10): array
  {
    $stmt = $this->pdo->prepare(
      'SELECT id, tipo, asunto, estado, creado_en, enviado_en FROM emails_cola WHERE id_usuario = :id ORDER BY id DESC LIMIT :limite'
    );
    $stmt->bindValue('id', $idUsuario, PDO::PARAM_INT);
    $stmt->bindValue('limite', $limite, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll();
  }

  /** Bloqueo con nombre para que dos procesos no envíen la misma cola a la vez. */
  public function bloquear(): bool
  {
    return (int) $this->valor("SELECT GET_LOCK('pfc_emails_cola', 0)") === 1;
  }

  public function liberar(): void
  {
    $this->valor("SELECT RELEASE_LOCK('pfc_emails_cola')");
  }
}
