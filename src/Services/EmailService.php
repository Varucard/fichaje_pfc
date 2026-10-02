<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\View;
use App\Repositories\EmailRepository;
use App\Repositories\UsuarioRepository;
use App\Support\Log;
use App\Support\Mailer;
use Throwable;

/**
 * Emails: se encolan en emails_cola y los envía bin/procesar-emails.php (o el botón
 * "Procesar ahora" del panel). Así una operación nunca espera ni falla por el correo.
 *
 * - Avisos (vencimiento, deuda, cumpleaños…): respetan la baja del usuario e incluyen
 *   el link para darse de baja.
 * - Transaccionales (comprobante, resumen, prueba): se envían siempre.
 * - clave_unica evita mandar dos veces el mismo aviso aunque el proceso corra varias veces.
 */
final class EmailService
{
  public const TIPOS = [
    'vencimiento' => 'Vencimiento próximo',
    'deuda' => 'Cuota vencida / deuda',
    'inactividad' => 'Te extrañamos',
    'cumpleanos' => 'Cumpleaños',
    'bienvenida' => 'Bienvenida',
    'comprobante' => 'Comprobante de pago',
    'resumen' => 'Resumen semanal',
    'prueba' => 'Prueba',
    'alerta' => 'Alerta del sistema',
  ];

  /** Tipos que no son avisos de marketing: se envían aunque el usuario se haya dado de baja. */
  private const TRANSACCIONALES = ['comprobante', 'resumen', 'prueba', 'alerta'];

  public function __construct(
    private readonly EmailRepository $emails,
    private readonly UsuarioRepository $usuarios,
    private readonly View $view,
    private readonly Mailer $mailer,
    private readonly ConfiguracionService $config,
    private readonly ComprobanteService $comprobantes,
  ) {
  }

  /**
   * Encola un email para un usuario. Devuelve false si no se encoló
   * (sin email, dado de baja o ya enviado con la misma clave).
   */
  public function encolarParaUsuario(
    string $tipo,
    array $usuario,
    string $asunto,
    array $datos = [],
    ?string $claveUnica = null,
    ?string $adjunto = null,
  ): bool {
    $email = trim((string) ($usuario['email'] ?? ''));
    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return false;
    }
    $esAviso = !in_array($tipo, self::TRANSACCIONALES, true);
    if ($esAviso && isset($usuario['acepta_emails']) && !(int) $usuario['acepta_emails']) {
      return false;
    }

    $datos['usuario'] = $usuario;
    if ($esAviso) {
      $datos['url_baja'] = url_absoluta('/emails/baja/' . $this->tokenBaja($usuario));
    }

    return $this->encolar($tipo, (int) $usuario['id_user'], $email, trim($usuario['user_name'] . ' ' . $usuario['user_surname']), $asunto, $datos, $claveUnica, $adjunto);
  }

  /** Encola un email a una dirección cualquiera (resumen para el admin, prueba). */
  public function encolarDirecto(string $tipo, string $email, string $asunto, array $datos = [], ?string $claveUnica = null): bool
  {
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      return false;
    }
    return $this->encolar($tipo, null, $email, null, $asunto, $datos, $claveUnica, null);
  }

  public function yaEncolado(string $claveUnica): bool
  {
    return $this->emails->yaExiste($claveUnica);
  }

  /**
   * Envía los emails pendientes. Si otro proceso ya está enviando, no hace nada.
   *
   * @return array{enviados: int, errores: int, ocupado?: bool}
   */
  public function procesarCola(int $limite = 50): array
  {
    if (!$this->emails->bloquear()) {
      return ['enviados' => 0, 'errores' => 0, 'ocupado' => true];
    }

    $resultado = ['enviados' => 0, 'errores' => 0];
    try {
      foreach ($this->emails->pendientes($limite) as $email) {
        try {
          $this->mailer->enviar(
            $email['destinatario'],
            $email['nombre_destinatario'],
            $email['asunto'],
            $email['cuerpo_html'],
            $email['cuerpo_texto'],
            $this->adjuntos($email['adjunto']),
            $this->encabezados($email),
          );
          $this->emails->marcarEnviado((int) $email['id']);
          $resultado['enviados']++;
        } catch (Throwable $e) {
          $this->emails->marcarError((int) $email['id'], $e->getMessage(), (int) Config::get('mail.max_intentos', 3));
          Log::warning('Falló el envío de un email', ['id' => $email['id'], 'tipo' => $email['tipo'], 'error' => $e->getMessage()]);
          $resultado['errores']++;
        }
      }
    } finally {
      $this->emails->liberar();
    }

    if ($resultado['enviados'] || $resultado['errores']) {
      Log::info('Cola de emails procesada', $resultado);
    }
    return $resultado;
  }

  public function listar(?string $estado, ?string $tipo, int $pagina, int $porPagina = 30): array
  {
    $pagina = max(1, $pagina);
    $resultado = $this->emails->listar($estado, $tipo, $pagina, $porPagina);
    $resultado['paginas'] = max(1, (int) ceil($resultado['total'] / $porPagina));
    $resultado['por_estado'] = $this->emails->contarPorEstado();
    return $resultado;
  }

  public function buscar(int $id): ?array
  {
    return $this->emails->buscarPorId($id);
  }

  public function reintentar(int $id): bool
  {
    return $this->emails->reintentar($id) > 0;
  }

  /** Cancela un email todavía no enviado (ej: el comprobante de un pago eliminado). */
  public function cancelarPorClave(string $claveUnica): void
  {
    $this->emails->cancelarPorClave($claveUnica);
  }

  public function cancelar(int $id): bool
  {
    return $this->emails->cancelar($id) > 0;
  }

  public function deUsuario(int $idUsuario): array
  {
    return $this->emails->deUsuario($idUsuario);
  }

  /** Convierte el HTML del email en texto plano (versión alternativa obligatoria). */
  public static function aTexto(string $html): string
  {
    $html = preg_replace('#<(style|script|head)[^>]*>.*?</\1>#si', '', $html) ?? $html;
    $html = preg_replace('#<a[^>]+href="([^"]+)"[^>]*>(.*?)</a>#si', '$2 ($1)', $html) ?? $html;
    $html = preg_replace('#<(br|/p|/tr|/h[1-6]|/li|/div)[^>]*>#i', "\n", $html) ?? $html;
    $texto = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $texto = preg_replace('/[ \t]+/', ' ', $texto) ?? $texto;
    $texto = preg_replace('/\n\s*\n\s*\n+/', "\n\n", $texto) ?? $texto;
    return trim(implode("\n", array_map('trim', explode("\n", $texto))));
  }

  private function encolar(
    string $tipo,
    ?int $idUsuario,
    string $email,
    ?string $nombre,
    string $asunto,
    array $datos,
    ?string $claveUnica,
    ?string $adjunto,
  ): bool {
    try {
      $html = $this->view->render('emails/' . $tipo, $datos + $this->datosComunes($asunto), 'emails/layout');
      $encolado = $this->emails->encolar([
        'tipo' => $tipo,
        'id_usuario' => $idUsuario,
        'destinatario' => $email,
        'nombre_destinatario' => $nombre,
        'asunto' => $asunto,
        'cuerpo_html' => $html,
        'cuerpo_texto' => self::aTexto($html),
        'adjunto' => $adjunto,
        'clave_unica' => $claveUnica,
      ]);
      if ($encolado) {
        Log::debug('Email encolado', ['tipo' => $tipo, 'para' => $email]);
      }
      return $encolado;
    } catch (Throwable $e) {
      // Un error de base de datos dentro de una transacción ya la revirtió: se propaga.
      if ($e instanceof \PDOException && $this->emails->enTransaccion()) {
        throw $e;
      }
      // Cualquier otro problema con el email nunca debe romper la operación que lo originó.
      Log::error("No se pudo encolar el email {$tipo}", $e, ['para' => $email]);
      return false;
    }
  }

  private function datosComunes(string $asunto): array
  {
    return [
      'asunto' => $asunto,
      'gimnasio' => [
        'nombre' => (string) Config::get('app.nombre'),
        'direccion' => $this->config->get('gimnasio.direccion'),
        'telefono' => $this->config->get('gimnasio.telefono'),
      ],
    ];
  }

  private function tokenBaja(array $usuario): string
  {
    if (!empty($usuario['token_baja'])) {
      return $usuario['token_baja'];
    }
    $this->usuarios->asignarTokenBaja((int) $usuario['id_user'], bin2hex(random_bytes(16)));
    // Se relee por si otro proceso asignó el token al mismo tiempo.
    return (string) $this->usuarios->buscarPorId((int) $usuario['id_user'])['token_baja'];
  }

  /** @return array<string, string> */
  private function adjuntos(?string $adjunto): array
  {
    if ($adjunto !== null && preg_match('/^comprobante:(\d+)$/', $adjunto, $m)) {
      [$archivo, $pdf] = $this->comprobantes->pdf((int) $m[1]);
      return [$archivo => $pdf];
    }
    return [];
  }

  /** @return array<string, string> */
  private function encabezados(array $email): array
  {
    if (in_array($email['tipo'], self::TRANSACCIONALES, true) || !$email['id_usuario']) {
      return [];
    }
    $usuario = $this->usuarios->buscarPorId((int) $email['id_usuario']);
    if (empty($usuario['token_baja'])) {
      return [];
    }
    // Permite que Gmail muestre el botón "Anular suscripción" (RFC 2369 / 8058).
    return [
      'List-Unsubscribe' => '<' . url_absoluta('/emails/baja/' . $usuario['token_baja'] . '/un-clic') . '>',
      'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
    ];
  }
}
