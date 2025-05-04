<?php

require_once '../models/uid_model.php';
require_once '../models/user_model.php';
require_once '../models/pago_model.php';
require_once '../models/clase_alumno_model.php';
require_once '../helpers/url_helper.php';
require_once 'fichaje_uid.php';

header('Content-Type: application/json');

$uidModel = new UID();
$userModel = new User();
$classUserModel = new ClaseAlumno();
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

// Buscar el usuario ACTIVO por el UID
$user = $userModel->getUserByRFID($uidData);

if (!$user) {
  // Sino se encuentra un ningún registro de un Usuario con el UID es desconocido, se inserta en la base de datos
  if (!$userModel->getUltimoRegistroPorRFID($uidData)) {
    $uidModel->insertUID($uidData);
  
    echo json_encode([
      'estado' => $estado,
      'nombre' => $nombre,
      'apellido' => $apellido,
    ], JSON_UNESCAPED_UNICODE);
  } else {
    echo json_encode([
      'estado' => "inactivo",
      'nombre' => $nombre,
      'apellido' => $apellido,
    ], JSON_UNESCAPED_UNICODE);
  }

} else {
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

  // Si el Usuario no es Alumno es un Admin o Profesor, pero vamos a tomarlo como un Admin (Es posible a futuro que esto se utilice para profesores)
  if ($user['type_user'] != 2) $estado = "admin";

  // Si el Usuario no tiene clases
  if (!$classUserModel->getClaseAlumnoByIdAlumno($user['id_user'])) $estado = "sinclase";

  // Si todo esta bien (Osea, es un Usuario con todo para fichar) registro su fichada
  if ($estado === "activo") {
    registrarFichajeManual($user['dni']);
  }

  // Enviar respuesta
  echo json_encode([
    'estado' => $estado,
    'nombre' => $user['user_name'],
    'apellido' => $user['user_surname'],
  ], JSON_UNESCAPED_UNICODE);
}

?>
