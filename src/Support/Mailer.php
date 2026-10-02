<?php

declare(strict_types=1);

namespace App\Support;

use App\Core\Config;
use PHPMailer\PHPMailer\Exception as PHPMailerException;
use PHPMailer\PHPMailer\PHPMailer;
use RuntimeException;

/**
 * Envío de emails por SMTP (Gmail u otro) con PHPMailer.
 *
 * En modo "log" (desarrollo) no envía nada: guarda cada mail como .eml en storage/emails,
 * que se puede abrir con cualquier cliente de correo.
 */
final class Mailer
{
  /**
   * @param array<string, string> $adjuntos nombreArchivo => contenido binario
   * @param array<string, string> $encabezados Encabezados extra (ej: List-Unsubscribe)
   */
  public function enviar(
    string $para,
    ?string $nombre,
    string $asunto,
    string $html,
    string $texto,
    array $adjuntos = [],
    array $encabezados = [],
  ): void {
    $mail = new PHPMailer(true);
    $mail->CharSet = PHPMailer::CHARSET_UTF8;
    $mail->Encoding = PHPMailer::ENCODING_BASE64;

    try {
      $mail->setFrom((string) Config::get('mail.remitente'), (string) Config::get('mail.remitente_nombre'));
      $mail->addAddress($para, (string) $nombre);
      $mail->Subject = $asunto;
      $mail->isHTML(true);
      $mail->Body = $html;
      $mail->AltBody = $texto;
      foreach ($adjuntos as $archivo => $contenido) {
        $mail->addStringAttachment($contenido, $archivo);
      }
      foreach ($encabezados as $clave => $valor) {
        $mail->addCustomHeader($clave, $valor);
      }

      if (Config::get('mail.modo') === 'log') {
        $this->guardarEnArchivo($mail, $para);
        return;
      }

      $mail->isSMTP();
      $mail->Host = (string) Config::get('mail.host');
      $mail->Port = (int) Config::get('mail.puerto');
      $mail->Timeout = 15;
      $usuario = (string) Config::get('mail.usuario');
      if ($usuario !== '') {
        $mail->SMTPAuth = true;
        $mail->Username = $usuario;
        $mail->Password = (string) Config::get('mail.password');
      }
      $seguridad = (string) Config::get('mail.seguridad');
      $mail->SMTPSecure = in_array($seguridad, ['tls', 'ssl'], true)
        ? ($seguridad === 'ssl' ? PHPMailer::ENCRYPTION_SMTPS : PHPMailer::ENCRYPTION_STARTTLS)
        : '';
      $mail->SMTPAutoTLS = $seguridad !== '';

      $mail->send();
    } catch (PHPMailerException $e) {
      throw new RuntimeException('No se pudo enviar el email: ' . $mail->ErrorInfo, 0, $e);
    }
  }

  private function guardarEnArchivo(PHPMailer $mail, string $para): void
  {
    $mail->preSend();
    $directorio = base_path('storage/emails');
    if (!is_dir($directorio)) {
      mkdir($directorio, 0775, true);
    }
    $archivo = sprintf('%s/%s_%s.eml', $directorio, date('Ymd_His'), preg_replace('/[^a-z0-9._-]+/i', '_', $para));
    file_put_contents($archivo, $mail->getSentMIMEMessage());
    Log::info('Email guardado en archivo (MAIL_MODO=log)', ['archivo' => basename($archivo), 'para' => $para]);
  }
}
