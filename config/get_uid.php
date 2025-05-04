<?php

require_once '../models/uid_model.php';
require_once '../models/user_model.php';
require_once '../models/pago_model.php';
require_once '../helpers/url_helper.php';

header('Content-Type: application/json');

$uidModel = new UID();
$userModel = new User();
$pagoModel = new Pagos();

// Validar el token de acceso del Arduino
if (!isset($_GET['auth']) || $_GET['auth'] !== 'ABC123') {
  http_response_code(403);
  echo json_encode(['status' => 'error', 'message' => 'Acceso denegado']);
  exit;
}

$uidData = isset($_GET['uid']) ? $_GET['uid'] : null;
$estado = "desconocido";
$nombre = "";
$apellido = "";

if (!$uidData) {
  http_response_code(400);
  echo json_encode([
    'status' => 'error',
    'message' => 'UID no proporcionado'
  ]);
  exit;
}

// Buscar el usuario por el UID
$user = $userModel->getUserByRFID($uidData);

// Sino se encuentra un Usuario con el UID significa que es desconocido, se inserta en la base de datos
if (!$user) {
  $uidModel->insertUID($uidData);

  echo json_encode([
    'estado' => $estado,
    'nombre' => $nombre,
    'apellido' => $apellido,
  ], JSON_UNESCAPED_UNICODE);

} else {
  // El usuario existe, proceder con el estado del usuario
  if ($user['asset'] != 1) {
    $estado = "inactivo";
  } else {
    // Usuario activo, verificar si tiene pago pendiente
    $estado = "activo";

    //! Agregar que tiene que estar en una clase "sinclase"
    //! Los Administradores no pasan por clase "admin"

    $fechaPago = $pagoModel->getUltimaFechaPago($user['id_user']);
    $fecha_actual = new DateTime();

    // Si no tiene ningun pago registrado es moroso tambien
    if ($fechaPago) {
      $fechaPago = new DateTime($fechaPago);
      if ($fecha_actual > $fechaPago) {
        $estado = "moroso";
      } else {
        $estado = "activo";
      }
    } else {
      $estado = "moroso";
    }
  }

  // Enviar respuesta
  echo json_encode([
    'estado' => $estado,
    'nombre' => $user['user_name'],
    'apellido' => $user['user_surname'],
  ], JSON_UNESCAPED_UNICODE);
}
?>
