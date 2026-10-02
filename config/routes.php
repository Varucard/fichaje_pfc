<?php

declare(strict_types=1);

use App\Controllers\Api\ArduinoController;
use App\Controllers\Api\PanelApiController;
use App\Controllers\AuditoriaController;
use App\Controllers\AuthController;
use App\Controllers\ClaseController;
use App\Controllers\DashboardController;
use App\Controllers\FichajeController;
use App\Controllers\PagoController;
use App\Controllers\SistemaController;
use App\Controllers\UsuarioController;
use App\Core\Router;

/*
 * Mapa de URLs de la aplicación. Las rutas se evalúan en orden.
 * Todas las rutas POST validan el token CSRF automáticamente.
 */
return function (Router $r): void {
  $r->get('/', [DashboardController::class, 'inicio']);

  // Autenticación
  $r->get('/login', [AuthController::class, 'mostrarLogin'], ['invitado']);
  $r->post('/login', [AuthController::class, 'login'], ['invitado']);

  // Lector RFID (autenticado por token, sin sesión)
  $r->get('/api/arduino/lectura', [ArduinoController::class, 'lectura']);
  // Compatibilidad con lectores que todavía tienen el firmware anterior.
  $r->get('/config/get_uid.php', [ArduinoController::class, 'lectura']);

  $r->grupo(['auth'], function (Router $r): void {
    $r->post('/logout', [AuthController::class, 'logout']);
    $r->get('/dashboard', [DashboardController::class, 'index']);

    // Clientes y profesores
    $r->get('/clientes', [UsuarioController::class, 'clientes']);
    $r->get('/profesores', [UsuarioController::class, 'profesores']);
    $r->get('/usuarios/buscar', [UsuarioController::class, 'buscar']);
    $r->get('/usuarios/nuevo', [UsuarioController::class, 'crear']);
    $r->post('/usuarios', [UsuarioController::class, 'guardar']);
    $r->get('/usuarios/{dni:\d+}', [UsuarioController::class, 'mostrar']);
    // Se actualiza por ID porque el DNI es uno de los datos editables.
    $r->post('/usuarios/{id:\d+}/actualizar', [UsuarioController::class, 'actualizar']);
    $r->post('/usuarios/{dni:\d+}/desactivar', [UsuarioController::class, 'desactivar']);
    $r->post('/usuarios/{dni:\d+}/reactivar', [UsuarioController::class, 'reactivar']);
    $r->post('/usuarios/{dni:\d+}/matricular', [ClaseController::class, 'matricular']);

    // Pagos
    $r->post('/usuarios/{dni:\d+}/pagos', [PagoController::class, 'renovar']);
    $r->post('/pagos/manual', [PagoController::class, 'manual']);
    $r->post('/pagos/{id:\d+}/eliminar', [PagoController::class, 'eliminar']);

    // Clases
    $r->get('/clases', [ClaseController::class, 'index']);
    $r->get('/clases/buscar', [ClaseController::class, 'buscar']);
    $r->get('/clases/nueva', [ClaseController::class, 'crear']);
    $r->post('/clases', [ClaseController::class, 'guardar']);
    $r->get('/clases/{id:\d+}', [ClaseController::class, 'mostrar']);
    $r->post('/clases/{id:\d+}/actualizar', [ClaseController::class, 'actualizar']);
    $r->post('/clases/{id:\d+}/eliminar', [ClaseController::class, 'eliminar']);
    $r->post('/clases/{id:\d+}/miembros', [ClaseController::class, 'agregarMiembro']);
    $r->post('/clases/{id:\d+}/miembros/{usuario:\d+}/quitar', [ClaseController::class, 'quitarMiembro']);

    // Fichajes
    $r->get('/fichajes', [FichajeController::class, 'index']);
    $r->get('/fichajes/buscar', [FichajeController::class, 'buscar']);
    $r->post('/fichajes/manual', [FichajeController::class, 'manual']);

    // Auditoría y logs
    $r->get('/auditoria', [AuditoriaController::class, 'index']);
    $r->get('/sistema/logs', [AuditoriaController::class, 'logs']);

    // Mantenimiento
    $r->post('/sistema/reiniciar-arduino', [SistemaController::class, 'reiniciarArduino']);
    $r->post('/sistema/backup', [SistemaController::class, 'backup']);

    // API interna del panel (JSON)
    $r->get('/api/fichajes/ultimos', [PanelApiController::class, 'ultimosFichajes']);
    $r->get('/api/usuarios/cumpleaneros', [PanelApiController::class, 'cumpleaneros']);
    $r->get('/api/llaveros/pendientes', [PanelApiController::class, 'llaverosPendientes']);
  });
};
