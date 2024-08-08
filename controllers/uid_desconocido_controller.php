<?php
require_once '../models/uid_model.php';
require_once '../models/user_model.php';
require_once '../models/pago_model.php';

header('Content-Type: application/json');

$uidModel = new UID();
$userModel = new User();
$pagoModel = new Pagos();

$uidData = $uidModel->getLastUID();
$mensaje = "Nuevo UID desconocido detectado: $uidData";

if ($uidData) {
  $user = $userModel->getUserByRFID($uidData);

  if ($user) {
    if ($user[0]['asset'] == 0) {
      $mensaje = "El usuario: {$user[0]['name']} {$user[0]['surname']} se encuentra inactivo. Llavero: $uidData";
    } else {
      $fechaPago = $pagoModel->getUltimaFechaPago($user[0]['id_user']);
      $fecha_actual = new DateTime();

      if ($fechaPago) {
        $fechaPago = new DateTime($fechaPago);
        if ($fecha_actual > $fechaPago) {
          $mensaje = "El usuario: {$user[0]['name']} {$user[0]['surname']} adeuda el pago de la cuota. Llavero: $uidData";
        }
      } else {
        $mensaje = "El usuario: {$user[0]['name']} {$user[0]['surname']} no posee fechas de pago, el mismo debe abonar para ingresar. Llavero: $uidData";
      }
    }
  } else {
    $mensaje = "Nuevo UID desconocido detectado: $uidData";
  }

  // Enviar el mensaje como respuesta JSON
  echo json_encode(['mensaje' => $mensaje]);

  // Eliminar el UID de la base de datos
  $uidModel->deleteUID($uidData);
} else {
  echo json_encode(['mensaje' => null]);
}
?>
