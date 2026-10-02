<?php

declare(strict_types=1);

namespace App\Services;

use App\Exceptions\ValidacionException;
use PDO;

/**
 * Configuración editable desde el panel (tabla configuracion), con valores por defecto.
 * Para valores de infraestructura (claves, servidores) se usa el .env.
 */
final class ConfiguracionService
{
  /**
   * Definición de cada opción: valor por defecto, tipo y etiqueta para el panel.
   * Tipos: bool | dias (entero 1-365) | dia_semana (1-7) | email | texto
   */
  public const OPCIONES = [
    'aviso.vencimiento.activo' => ['1', 'bool', 'Aviso de vencimiento próximo'],
    'aviso.vencimiento.dias' => ['3', 'dias', 'Días antes del vencimiento'],
    'aviso.deuda.activo' => ['1', 'bool', 'Recordatorio de cuota vencida / deuda'],
    'aviso.deuda.cada_dias' => ['7', 'dias', 'Repetir el recordatorio cada (días)'],
    'aviso.inactividad.activo' => ['1', 'bool', '"Te extrañamos" por inasistencia'],
    'aviso.inactividad.dias' => ['14', 'dias', 'Días sin venir para enviar el aviso'],
    'aviso.cumpleanos.activo' => ['1', 'bool', 'Saludo de cumpleaños'],
    'aviso.bienvenida.activo' => ['1', 'bool', 'Bienvenida al dar de alta un cliente'],
    'aviso.comprobante.activo' => ['1', 'bool', 'Comprobante de pago (PDF)'],
    'aviso.resumen.activo' => ['1', 'bool', 'Resumen semanal para el administrador'],
    'aviso.resumen.dia' => ['1', 'dia_semana', 'Día del resumen semanal'],
    'aviso.resumen.destinatario' => ['', 'email', 'Email del administrador (resumen y alertas)'],
    'gimnasio.direccion' => ['', 'texto', 'Dirección (aparece en mails y comprobantes)'],
    'gimnasio.telefono' => ['', 'texto', 'Teléfono / WhatsApp'],
  ];

  public const DIAS_SEMANA = [1 => 'Lunes', 2 => 'Martes', 3 => 'Miércoles', 4 => 'Jueves', 5 => 'Viernes', 6 => 'Sábado', 7 => 'Domingo'];

  private ?array $valores = null;

  public function __construct(
    private readonly PDO $pdo,
    private readonly AuditoriaService $auditoria,
  ) {
  }

  public function get(string $clave): string
  {
    $this->valores ??= $this->pdo->query('SELECT clave, valor FROM configuracion')->fetchAll(PDO::FETCH_KEY_PAIR);
    return (string) ($this->valores[$clave] ?? self::OPCIONES[$clave][0] ?? '');
  }

  public function activo(string $clave): bool
  {
    return $this->get($clave) === '1';
  }

  public function entero(string $clave): int
  {
    return (int) $this->get($clave);
  }

  /** @return array<string, string> */
  public function todas(): array
  {
    $valores = [];
    foreach (array_keys(self::OPCIONES) as $clave) {
      $valores[$clave] = $this->get($clave);
    }
    return $valores;
  }

  /** Guarda un valor interno del sistema (no editable desde el panel, sin auditoría). */
  public function establecer(string $clave, string $valor): void
  {
    $this->pdo->prepare('REPLACE INTO configuracion (clave, valor) VALUES (?, ?)')->execute([$clave, $valor]);
    if ($this->valores !== null) {
      $this->valores[$clave] = $valor;
    }
  }

  /** Guarda las opciones del formulario del panel. Los checkboxes ausentes se toman como apagados. */
  public function guardar(array $entrada): void
  {
    $nuevos = [];
    foreach (self::OPCIONES as $clave => [, $tipo, $etiqueta]) {
      $campo = str_replace('.', '_', $clave);
      $valor = trim((string) ($entrada[$campo] ?? ''));
      $nuevos[$clave] = match ($tipo) {
        'bool' => $valor === '1' ? '1' : '0',
        'dias' => ctype_digit($valor) && (int) $valor >= 1 && (int) $valor <= 365
          ? $valor : throw new ValidacionException("{$etiqueta}: ingresá un número de días entre 1 y 365."),
        'dia_semana' => isset(self::DIAS_SEMANA[(int) $valor]) ? (string) (int) $valor
          : throw new ValidacionException("{$etiqueta}: día inválido."),
        'email' => $valor === '' || filter_var($valor, FILTER_VALIDATE_EMAIL)
          ? $valor : throw new ValidacionException("{$etiqueta}: el email no es válido."),
        default => mb_substr($valor, 0, 255),
      };
    }

    $anteriores = $this->todas();
    $stmt = $this->pdo->prepare('REPLACE INTO configuracion (clave, valor) VALUES (?, ?)');
    foreach ($nuevos as $clave => $valor) {
      $stmt->execute([$clave, $valor]);
    }
    $this->valores = null;

    $cambios = AuditoriaService::cambios($anteriores, $nuevos, array_keys($nuevos));
    if ($cambios) {
      $this->auditoria->registrar('sistema.configuracion', 'Cambio de configuración: ' . implode(', ', array_keys($cambios)), 'configuracion', null, $cambios);
    }
  }
}
