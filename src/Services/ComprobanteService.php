<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Core\View;
use App\Exceptions\ValidacionException;
use App\Repositories\PagoRepository;
use App\Repositories\UsuarioRepository;
use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Comprobante de pago en PDF (recibo sin validez fiscal).
 * El número de comprobante es el ID del pago.
 */
final class ComprobanteService
{
  public function __construct(
    private readonly PagoRepository $pagos,
    private readonly UsuarioRepository $usuarios,
    private readonly View $view,
    private readonly ConfiguracionService $config,
  ) {
  }

  public static function numero(int $idPago): string
  {
    return str_pad((string) $idPago, 8, '0', STR_PAD_LEFT);
  }

  /** Datos para armar el comprobante. */
  public function datos(int $idPago): array
  {
    $pago = $this->pagos->buscarPorId($idPago) ?? throw new ValidacionException('El pago no existe.');
    $usuario = $this->usuarios->buscarPorId((int) $pago['id_user']) ?? throw new ValidacionException('El usuario del pago no existe.');

    return [
      'pago' => $pago,
      'usuario' => $usuario,
      'detalle' => $this->pagos->detalle($idPago),
      'numero' => self::numero($idPago),
      'gimnasio' => [
        'nombre' => (string) Config::get('app.nombre'),
        'direccion' => $this->config->get('gimnasio.direccion'),
        'telefono' => $this->config->get('gimnasio.telefono'),
      ],
    ];
  }

  /** @return array{0: string, 1: string} [nombre de archivo, contenido PDF] */
  public function pdf(int $idPago): array
  {
    $datos = $this->datos($idPago);
    $logo = base_path('public/img/logo.png');
    $datos['logo'] = is_file($logo) && extension_loaded('gd')
      ? 'data:image/png;base64,' . base64_encode((string) file_get_contents($logo))
      : null;

    $opciones = new Options();
    $opciones->set('isRemoteEnabled', false);
    $opciones->set('defaultFont', 'DejaVu Sans');

    $dompdf = new Dompdf($opciones);
    $dompdf->loadHtml($this->view->render('comprobantes/pago', $datos, null), 'UTF-8');
    $dompdf->setPaper('A5', 'portrait');
    $dompdf->render();

    return ["comprobante-{$datos['numero']}.pdf", (string) $dompdf->output()];
  }
}
