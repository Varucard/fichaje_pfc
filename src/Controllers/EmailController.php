<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Request;
use App\Exceptions\HttpException;
use App\Exceptions\ValidacionException;
use App\Repositories\UsuarioRepository;
use App\Services\AuditoriaService;
use App\Services\AvisosService;
use App\Services\ConfiguracionService;
use App\Services\EmailService;

final class EmailController extends Controller
{
  public function __construct(
    private readonly EmailService $emails,
    private readonly AvisosService $avisos,
    private readonly ConfiguracionService $config,
    private readonly UsuarioRepository $usuarios,
    private readonly AuditoriaService $auditoria,
  ) {
  }

  /** Configuración de avisos y estado de la cola de envíos. */
  public function index(Request $request): void
  {
    $estado = in_array($request->query('estado'), ['pendiente', 'enviado', 'error', 'cancelado'], true) ? $request->query('estado') : null;
    $tipo = array_key_exists((string) $request->query('tipo'), EmailService::TIPOS) ? $request->query('tipo') : null;
    $pagina = max(1, (int) $request->query('pagina', '1'));

    $this->render('emails/panel', $this->emails->listar($estado, $tipo, $pagina) + [
      'titulo' => 'Emails',
      'config' => $this->config->todas(),
      'opciones' => ConfiguracionService::OPCIONES,
      'dias' => ConfiguracionService::DIAS_SEMANA,
      'tipos' => EmailService::TIPOS,
      'filtros' => ['estado' => $estado, 'tipo' => $tipo],
      'pagina' => $pagina,
    ], 'layouts/main');
  }

  public function guardarConfiguracion(Request $request): void
  {
    try {
      $this->config->guardar($request->todos());
    } catch (ValidacionException $e) {
      $this->error($e->getMessage(), '/emails');
    }
    $this->exito('Configuración guardada.', '/emails');
  }

  public function prueba(Request $request): void
  {
    $destino = (string) $request->input('destinatario', '');
    if (!$this->emails->encolarDirecto('prueba', $destino, 'Email de prueba - Sistema de fichajes')) {
      $this->error('Ingresá un email válido.', '/emails');
    }
    $resultado = $this->emails->procesarCola();
    $resultado['errores']
      ? $this->error('No se pudo enviar el email de prueba. Revisá el detalle en la cola y la configuración MAIL_* del .env.', '/emails')
      : $this->exito("Email de prueba enviado a {$destino}.", '/emails');
  }

  /** Genera los avisos del día y envía la cola (lo mismo que hace el proceso programado). */
  public function procesar(Request $request): void
  {
    $encolados = array_sum($this->avisos->generar());
    $resultado = $this->emails->procesarCola();
    $this->auditoria->registrar('sistema.emails', "Procesamiento manual de emails: {$encolados} nuevos, {$resultado['enviados']} enviados, {$resultado['errores']} con error");

    if (!empty($resultado['ocupado'])) {
      $this->error('Otro proceso está enviando emails en este momento. Probá en unos minutos.', '/emails');
    }
    $this->exito("Avisos nuevos: {$encolados}. Enviados: {$resultado['enviados']}. Con error: {$resultado['errores']}.", '/emails');
  }

  public function ver(Request $request, string $id): void
  {
    $email = $this->emails->buscar((int) $id) ?? throw new HttpException(404, 'El email no existe.');
    // Se muestra aislado: sin scripts ni acceso al resto del panel.
    header("Content-Security-Policy: sandbox; default-src 'none'; img-src data:; style-src 'unsafe-inline'");
    header('Content-Type: text/html; charset=utf-8');
    echo $email['cuerpo_html'];
  }

  public function reintentar(Request $request, string $id): void
  {
    $this->emails->reintentar((int) $id)
      ? $this->exito('El email volvió a la cola.', '/emails')
      : $this->error('Solo se pueden reintentar emails con error o cancelados.', '/emails');
  }

  public function cancelar(Request $request, string $id): void
  {
    $this->emails->cancelar((int) $id)
      ? $this->exito('Email cancelado.', '/emails')
      : $this->error('Solo se pueden cancelar emails pendientes.', '/emails');
  }

  /** Activa o desactiva los avisos de un usuario desde su ficha. */
  public function preferencia(Request $request, string $dni): void
  {
    $usuario = $this->usuarios->buscarPorDni($dni) ?? throw new HttpException(404, 'El usuario no existe.');
    $acepta = $request->input('acepta') === '1';
    $this->usuarios->cambiarAceptaEmails((int) $usuario['id_user'], $acepta);
    $this->auditoria->registrar('usuario.emails', ($acepta ? 'Activación' : 'Baja') . " de avisos por email de {$usuario['user_name']} {$usuario['user_surname']}", 'usuario', $dni);
    $this->exito($acepta ? 'El usuario vuelve a recibir avisos.' : 'El usuario ya no recibirá avisos.', '/usuarios/' . $dni);
  }

  /** Página pública para darse de baja desde el link del email (sin sesión). */
  public function mostrarBaja(Request $request, string $token): void
  {
    $usuario = $this->usuarios->buscarPorTokenBaja($token) ?? throw new HttpException(404, 'El link no es válido.');
    $this->render('publico/baja', ['titulo' => 'Avisos por email', 'usuario' => $usuario, 'token' => $token], 'layouts/simple');
  }

  public function confirmarBaja(Request $request, string $token): void
  {
    $usuario = $this->usuarios->buscarPorTokenBaja($token) ?? throw new HttpException(404, 'El link no es válido.');
    $acepta = $request->input('acepta') === '1';
    $this->usuarios->cambiarAceptaEmails((int) $usuario['id_user'], $acepta);
    $this->auditoria->registrar('usuario.emails', ($acepta ? 'Reactivación' : 'Baja') . " de avisos por email desde el link del mail ({$usuario['user_name']})", 'usuario', $usuario['dni'], actor: $usuario['user_name']);

    $this->render('publico/baja', [
      'titulo' => 'Avisos por email',
      'usuario' => ['acepta_emails' => $acepta ? 1 : 0] + $usuario,
      'token' => $token,
      'confirmado' => true,
    ], 'layouts/simple');
  }
}
