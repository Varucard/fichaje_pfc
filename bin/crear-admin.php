<?php

declare(strict_types=1);

/*
 * Crea un administrador o cambia la contraseña de un usuario existente.
 *
 * Uso:  php bin/crear-admin.php
 *       (o dentro de Docker: docker compose exec php php bin/crear-admin.php)
 */

use App\Core\App;
use App\Domain\Llavero;
use App\Domain\TipoUsuario;
use App\Repositories\UsuarioRepository;

if (PHP_SAPI !== 'cli') {
  exit("Este script solo se ejecuta desde la consola.\n");
}

require dirname(__DIR__) . '/vendor/autoload.php';
App::configurar();

function preguntar(string $texto, bool $oculto = false): string
{
  echo $texto;
  $puedeOcultar = $oculto && DIRECTORY_SEPARATOR === '/' && stream_isatty(STDIN);
  if ($puedeOcultar) {
    shell_exec('stty -echo');
  }
  $valor = trim((string) fgets(STDIN));
  if ($puedeOcultar) {
    shell_exec('stty echo');
    echo PHP_EOL;
  }
  return $valor;
}

$usuarios = App::container()->get(UsuarioRepository::class);

$dni = preg_replace('/\D/', '', preguntar('DNI del administrador: '));
if (strlen($dni) < 7 || strlen($dni) > 8) {
  exit("El DNI debe tener 7 u 8 dígitos.\n");
}

$password = preguntar('Contraseña (mínimo 8 caracteres): ', true);
if (mb_strlen($password) < 8) {
  exit("La contraseña es demasiado corta.\n");
}
if (preguntar('Repetir contraseña: ', true) !== $password) {
  exit("Las contraseñas no coinciden.\n");
}

$hash = password_hash($password, PASSWORD_DEFAULT);
$existente = $usuarios->buscarPorDni($dni);

if ($existente) {
  $usuarios->actualizarPassword((int) $existente['id_user'], $hash);
  echo "Contraseña actualizada para {$existente['user_name']} (DNI {$dni}).\n";
  if (!(int) $existente['asset']) {
    $usuarios->cambiarEstado((int) $existente['id_user'], true);
    echo "El usuario estaba inactivo: se reactivó para que pueda iniciar sesión.\n";
  }
  if (TipoUsuario::deUsuario($existente) !== TipoUsuario::Administrador) {
    echo "Atención: el usuario no es de tipo ADMINISTRADOR, pero ya puede iniciar sesión.\n";
  }
  exit(0);
}

$nombre = preguntar('Nombre: ') ?: 'Administrador';
$apellido = preguntar('Apellido (opcional): ');

$id = $usuarios->crear([
  'rfid' => Llavero::SIN_LLAVERO,
  'dni' => $dni,
  'nombre' => $nombre,
  'apellido' => $apellido,
  'nacimiento' => '',
  'email' => '',
  'telefono' => '',
  'tipo' => TipoUsuario::Administrador,
]);
$usuarios->actualizarPassword($id, $hash);

echo "Administrador creado (ID {$id}). Ya podés iniciar sesión con el DNI {$dni}.\n";
