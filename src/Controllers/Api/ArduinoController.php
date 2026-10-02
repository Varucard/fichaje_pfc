<?php

declare(strict_types=1);

namespace App\Controllers\Api;

use App\Controllers\Controller;
use App\Core\Config;
use App\Core\Request;
use App\Exceptions\HttpException;
use App\Services\FichajeService;

/**
 * Endpoint que consulta el lector RFID cada vez que se pasa un llavero.
 * GET /api/arduino/lectura?uid=XXXX&auth=TOKEN
 * Responde: {"estado": "...", "nombre": "...", "apellido": "..."}
 */
final class ArduinoController extends Controller
{
  public function __construct(private readonly FichajeService $fichajes)
  {
  }

  public function lectura(Request $request): void
  {
    $token = (string) Config::get('arduino.token');
    if ($token === '') {
      throw new HttpException(503, 'ARDUINO_TOKEN no está configurado en el servidor');
    }
    if (!hash_equals($token, (string) $request->query('auth', ''))) {
      throw new HttpException(403, 'Acceso denegado');
    }

    $uid = (string) $request->query('uid', '');
    if ($uid === '' || !preg_match('/^[0-9A-Fa-f]{4,20}$/', $uid)) {
      throw new HttpException(400, 'UID no proporcionado o inválido');
    }

    $respuesta = $this->fichajes->procesarLectura($uid);
    $respuesta['nombre'] = self::textoLcd($respuesta['nombre']);
    $respuesta['apellido'] = self::textoLcd($respuesta['apellido']);
    $respuesta['clase'] = self::textoLcd($respuesta['clase'] ?? '');

    $this->json($respuesta);
  }

  /**
   * El LCD 20x4 no tiene tildes ni ñ y cada línea admite 20 caracteres:
   * "José Muñoz" => "Jose Munoz".
   */
  public static function textoLcd(string $texto): string
  {
    $texto = strtr($texto, [
      'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u', 'ü' => 'u', 'ñ' => 'n',
      'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N',
    ]);
    $texto = preg_replace('/[^\x20-\x7E]/', '', $texto) ?? '';
    return substr(trim($texto), 0, 20);
  }
}
