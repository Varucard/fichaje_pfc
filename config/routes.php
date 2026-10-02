<?php

declare(strict_types=1);

use App\Controllers\Api\ArduinoController;
use App\Controllers\Api\PanelApiController;
use App\Controllers\AdministradorController;
use App\Controllers\AuditoriaController;
use App\Controllers\AuthController;
use App\Controllers\ClaseController;
use App\Controllers\DashboardController;
use App\Controllers\DeudaController;
use App\Controllers\EmailController;
use App\Controllers\ExportController;
use App\Controllers\FichajeController;
use App\Controllers\LiquidacionController;
use App\Controllers\PagoController;
use App\Controllers\PromocionController;
use App\Controllers\ReporteController;
use App\Controllers\SistemaController;
use App\Controllers\StockController;
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

  // Baja de avisos por email (link público incluido en cada aviso)
  $r->get('/emails/baja/{token:[0-9a-f]{32}}', [EmailController::class, 'mostrarBaja']);
  $r->post('/emails/baja/{token:[0-9a-f]{32}}', [EmailController::class, 'confirmarBaja']);
  // Baja en un clic (RFC 8058): la envía Gmail sin token CSRF; la autentica el token del link.
  $r->post('/emails/baja/{token:[0-9a-f]{32}}/un-clic', [EmailController::class, 'bajaUnClic'], ['sin_csrf']);
  $r->get('/emails/baja/{token:[0-9a-f]{32}}/un-clic', [EmailController::class, 'mostrarBaja']);

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
    $r->get('/pagos/{id:\d+}/comprobante', [PagoController::class, 'comprobante']);

    // Promociones
    $r->get('/promociones', [PromocionController::class, 'index']);
    $r->post('/promociones', [PromocionController::class, 'guardar']);
    $r->post('/promociones/{id:\d+}/activa', [PromocionController::class, 'cambiarActiva']);

    // Deudas
    $r->get('/deudores', [DeudaController::class, 'index']);

    // Liquidación de profesores
    $r->get('/liquidaciones', [LiquidacionController::class, 'index']);
    $r->post('/liquidaciones', [LiquidacionController::class, 'registrar']);
    $r->post('/liquidaciones/{id:\d+}/pagar', [LiquidacionController::class, 'pagar']);
    $r->post('/liquidaciones/{id:\d+}/anular', [LiquidacionController::class, 'anular']);
    $r->post('/liquidaciones/profesores/{id:\d+}/configuracion', [LiquidacionController::class, 'configuracion']);

    // Stock
    $r->get('/stock', [StockController::class, 'index']);
    $r->get('/stock/nuevo', [StockController::class, 'crear']);
    $r->post('/stock', [StockController::class, 'guardar']);
    $r->get('/stock/{id:\d+}', [StockController::class, 'mostrar']);
    $r->post('/stock/{id:\d+}/actualizar', [StockController::class, 'actualizar']);
    $r->post('/stock/{id:\d+}/activo', [StockController::class, 'cambiarActivo']);
    $r->post('/stock/{id:\d+}/movimientos', [StockController::class, 'movimiento']);

    // Clases
    $r->get('/clases', [ClaseController::class, 'index']);
    $r->get('/clases/buscar', [ClaseController::class, 'buscar']);
    $r->get('/clases/nueva', [ClaseController::class, 'crear']);
    $r->post('/clases', [ClaseController::class, 'guardar']);
    $r->get('/clases/{id:\d+}', [ClaseController::class, 'mostrar']);
    $r->post('/clases/{id:\d+}/actualizar', [ClaseController::class, 'actualizar']);
    $r->post('/clases/{id:\d+}/eliminar', [ClaseController::class, 'eliminar']);
    $r->post('/clases/{id:\d+}/miembros', [ClaseController::class, 'agregarMiembro']);
    $r->post('/clases/{id:\d+}/horarios', [ClaseController::class, 'agregarHorario']);
    $r->post('/clases/{id:\d+}/horarios/{horario:\d+}/quitar', [ClaseController::class, 'quitarHorario']);
    $r->post('/clases/{id:\d+}/miembros/{usuario:\d+}/quitar', [ClaseController::class, 'quitarMiembro']);

    // Fichajes
    $r->get('/fichajes', [FichajeController::class, 'index']);
    $r->get('/fichajes/buscar', [FichajeController::class, 'buscar']);
    $r->post('/fichajes/manual', [FichajeController::class, 'manual']);

    // Emails
    $r->get('/emails', [EmailController::class, 'index']);
    $r->post('/emails/configuracion', [EmailController::class, 'guardarConfiguracion']);
    $r->post('/emails/prueba', [EmailController::class, 'prueba']);
    $r->post('/emails/procesar', [EmailController::class, 'procesar']);
    $r->get('/emails/{id:\d+}', [EmailController::class, 'ver']);
    $r->post('/emails/{id:\d+}/reintentar', [EmailController::class, 'reintentar']);
    $r->post('/emails/{id:\d+}/cancelar', [EmailController::class, 'cancelar']);
    $r->post('/usuarios/{dni:\d+}/emails', [EmailController::class, 'preferencia']);

    // Reportes y exportaciones (CSV para Excel)
    $r->get('/reportes/caja', [ReporteController::class, 'caja']);
    $r->get('/exportar/deudores.csv', [ExportController::class, 'deudores']);
    $r->get('/exportar/{tipo:clientes|profesores}.csv', [ExportController::class, 'usuarios']);
    $r->get('/exportar/pagos.csv', [ExportController::class, 'pagos']);
    $r->get('/exportar/liquidaciones.csv', [ExportController::class, 'liquidaciones']);
    $r->get('/exportar/caja.csv', [ExportController::class, 'caja']);

    // Administradores y cuenta propia
    $r->get('/administradores', [AdministradorController::class, 'index']);
    $r->post('/administradores', [AdministradorController::class, 'guardar']);
    $r->post('/administradores/{id:\d+}/password', [AdministradorController::class, 'password']);
    $r->post('/administradores/{id:\d+}/activo', [AdministradorController::class, 'cambiarActivo']);
    $r->get('/mi-cuenta', [AdministradorController::class, 'miCuenta']);
    $r->post('/mi-cuenta/password', [AdministradorController::class, 'miPassword']);

    // Auditoría y logs
    $r->get('/auditoria', [AuditoriaController::class, 'index']);
    $r->get('/sistema/logs', [AuditoriaController::class, 'logs']);

    // Mantenimiento
    $r->post('/sistema/reiniciar-arduino', [SistemaController::class, 'reiniciarArduino']);
    $r->post('/sistema/backup', [SistemaController::class, 'backup']);
    $r->get('/sistema/backups', [SistemaController::class, 'backups']);
    $r->get('/sistema/backups/{nombre:backup_[0-9_-]+\.sql}', [SistemaController::class, 'descargarBackup']);
    $r->get('/api/lector/estado', [SistemaController::class, 'estadoLector']);

    // API interna del panel (JSON)
    $r->get('/api/fichajes/ultimos', [PanelApiController::class, 'ultimosFichajes']);
    $r->get('/api/usuarios/cumpleaneros', [PanelApiController::class, 'cumpleaneros']);
    $r->get('/api/llaveros/pendientes', [PanelApiController::class, 'llaverosPendientes']);
  });
};
