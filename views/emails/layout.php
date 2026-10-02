<?php
/**
 * Layout de todos los emails. Usa estilos inline y tablas porque muchos clientes de correo
 * (Gmail, Outlook) ignoran las hojas de estilo.
 * @var string $contenido
 * @var string $asunto
 * @var array $gimnasio
 * @var string|null $url_baja
 */
$colorBoton = '#e5484d';
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title><?= e($asunto) ?></title>
</head>
<body style="margin:0;padding:0;background:#f2f3f5;font-family:Arial,Helvetica,sans-serif;color:#222;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f2f3f5;padding:24px 0;">
    <tr>
      <td align="center">
        <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px;width:100%;background:#ffffff;border-radius:8px;overflow:hidden;">
          <tr>
            <td style="background:#111132;color:#ffffff;padding:20px 28px;font-size:22px;font-weight:bold;">
              🥋 <?= e($gimnasio['nombre']) ?>
            </td>
          </tr>
          <tr>
            <td style="padding:28px;font-size:15px;line-height:1.6;">
              <?= $contenido ?>
            </td>
          </tr>
          <tr>
            <td style="background:#f7f7f9;padding:18px 28px;font-size:12px;color:#777;line-height:1.5;">
              <?= e($gimnasio['nombre']) ?>
              <?php if ($gimnasio['direccion']): ?> · <?= e($gimnasio['direccion']) ?><?php endif; ?>
              <?php if ($gimnasio['telefono']): ?> · <?= e($gimnasio['telefono']) ?><?php endif; ?>
              <?php if (!empty($url_baja)): ?>
                <br>¿No querés recibir más estos avisos? <a href="<?= e($url_baja) ?>" style="color:#777;">Darte de baja</a>.
              <?php endif; ?>
            </td>
          </tr>
        </table>
      </td>
    </tr>
  </table>
</body>
</html>
