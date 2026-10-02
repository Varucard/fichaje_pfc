<?php
/** Fila destacada "etiqueta: valor" dentro de un email. @var string $etiqueta @var string $valor @var bool|null $alerta */
?>
<tr>
  <td style="padding:8px 12px;border-bottom:1px solid #eee;color:#555;"><?= e($etiqueta) ?></td>
  <td style="padding:8px 12px;border-bottom:1px solid #eee;text-align:right;font-weight:bold;<?= !empty($alerta) ? 'color:#e5484d;' : '' ?>"><?= e($valor) ?></td>
</tr>
