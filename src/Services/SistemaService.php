<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Config;
use App\Exceptions\ValidacionException;
use App\Support\Log;
use PDO;

/**
 * Tareas de mantenimiento disparadas desde el panel: reiniciar el lector y respaldar la base.
 */
final class SistemaService
{
  public function __construct(private readonly PDO $pdo)
  {
  }

  public function reiniciarArduino(): void
  {
    $ip = (string) Config::get('arduino.ip');
    if ($ip === '') {
      throw new ValidacionException('No está configurada la IP del Arduino (IP_ARDUINO en .env).');
    }
    if (!function_exists('curl_init')) {
      throw new ValidacionException('La extensión cURL de PHP no está habilitada.');
    }

    $ch = curl_init(sprintf('http://%s:%d/reiniciar', $ip, (int) Config::get('arduino.puerto')));
    curl_setopt_array($ch, [
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_TIMEOUT => 1, // El Arduino se reinicia sin responder: no esperamos.
    ]);
    curl_exec($ch);
    curl_close($ch);

    Log::info("Reinicio del Arduino solicitado ({$ip})");
  }

  /**
   * Genera un respaldo .sql de la base. Intenta con mysqldump y, si no está disponible,
   * hace un volcado básico con PHP. Devuelve la ruta del archivo generado.
   */
  public function generarBackup(): string
  {
    $directorio = rtrim((string) Config::get('backup_dir'), '/');
    if (!is_dir($directorio) && !mkdir($directorio, 0775, true) && !is_dir($directorio)) {
      throw new ValidacionException("No se pudo crear la carpeta de respaldos: {$directorio}");
    }

    $archivo = sprintf('%s/backup_%s.sql', $directorio, date('Y-m-d_H-i-s'));

    if (!$this->backupConMysqldump($archivo)) {
      $this->backupConPhp($archivo);
    }

    Log::info("Respaldo generado: {$archivo}");
    return $archivo;
  }

  private function backupConMysqldump(string $archivo): bool
  {
    if (!function_exists('proc_open')) {
      return false;
    }

    $db = Config::get('db');
    $comando = sprintf(
      'mysqldump --single-transaction --routines --host=%s --port=%d --user=%s %s',
      escapeshellarg($db['host']),
      (int) $db['puerto'],
      escapeshellarg($db['usuario']),
      escapeshellarg($db['nombre'])
    );

    // La contraseña va por variable de entorno, no en la línea de comandos.
    $proceso = @proc_open(
      $comando,
      [1 => ['file', $archivo, 'w'], 2 => ['pipe', 'w']],
      $pipes,
      null,
      array_merge(getenv(), ['MYSQL_PWD' => $db['password']])
    );
    if (!is_resource($proceso)) {
      return false;
    }

    $errores = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $codigo = proc_close($proceso);

    if ($codigo !== 0) {
      Log::info('mysqldump no disponible o falló, se usa respaldo PHP: ' . trim((string) $errores));
      @unlink($archivo);
      return false;
    }

    $this->quitarModoSandboxMariaDb($archivo);
    return filesize($archivo) > 0;
  }

  /**
   * El mysqldump de MariaDB 11 agrega "/*M!999999\- enable the sandbox mode *\/" en la primera
   * línea, y el cliente de MySQL no puede restaurar un archivo que la contenga.
   */
  private function quitarModoSandboxMariaDb(string $archivo): void
  {
    $entrada = fopen($archivo, 'rb');
    $primeraLinea = (string) fgets($entrada);
    if (!str_contains($primeraLinea, 'enable the sandbox mode')) {
      fclose($entrada);
      return;
    }

    $temporal = $archivo . '.tmp';
    $salida = fopen($temporal, 'wb');
    stream_copy_to_stream($entrada, $salida);
    fclose($entrada);
    fclose($salida);
    rename($temporal, $archivo);
  }

  private function backupConPhp(string $archivo): void
  {
    $salida = fopen($archivo, 'wb') ?: throw new ValidacionException('No se pudo escribir el respaldo.');
    fwrite($salida, "SET FOREIGN_KEY_CHECKS=0;\n\n");

    foreach ($this->pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN) as $tabla) {
      $creacion = $this->pdo->query("SHOW CREATE TABLE `{$tabla}`")->fetch(PDO::FETCH_NUM);
      fwrite($salida, "DROP TABLE IF EXISTS `{$tabla}`;\n{$creacion[1]};\n\n");

      $filas = $this->pdo->query("SELECT * FROM `{$tabla}`", PDO::FETCH_NUM);
      foreach ($filas as $fila) {
        $valores = array_map(fn ($v) => $v === null ? 'NULL' : $this->pdo->quote((string) $v), $fila);
        fwrite($salida, "INSERT INTO `{$tabla}` VALUES (" . implode(', ', $valores) . ");\n");
      }
      fwrite($salida, "\n");
    }

    fwrite($salida, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($salida);
  }
}
