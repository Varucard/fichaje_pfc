<?php

require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

$user = new User();

// Captura los datos del formulario
$id = $_POST['id'];
$rfid = $_POST['rfid'];
$dni = $_POST['dni'];
$name = $_POST['name'];
$surname = $_POST['surname'];
$birth_day = $_POST['birth_day'];
$email = $_POST['email'];
$phone = $_POST['phone'];

// Verifico si el DNI ya está registrado en otro usuario activo
$aux = $user->getUserByDNI($dni);
if ($aux && $aux[0]['id_user'] != $id && $aux[0]['asset'] == 1) {
    echo "<script>alert('El Nro. de Documento ya se encuentra registrado en otro Usuario activo'); window.location.href = '../views/usuario_view.php?dni=" . htmlspecialchars($dni) . "';</script>";
    exit; 
}

// Verifico si el RFID ya está registrado en otro usuario activo, excluyendo el caso de "SIN LLAVERO"
$auxs_rfid = $user->getUserByRFID($rfid);
if ($rfid != 'SIN LLAVERO' && $auxs_rfid) {
  foreach ($auxs_rfid as $aux_rfid) {
    if ($aux_rfid['id_user'] != $id && $aux_rfid['asset'] == 1) {
      echo "<script>alert('El llavero ya se encuentra registrado en un Usuario activo'); window.location.href = '../views/usuario_view.php?dni=" . htmlspecialchars($dni) . "';</script>";
      exit; 
    }
  }
}

// Crea un array con los nuevos datos del usuario
$nuevosDatos = [
  'id' => $id,
  'name' => $name,
  'surname' => $surname,
  'birth_day' => $birth_day,
  'rfid' => $rfid,
  'dni' => $dni,
  'email' => $email,
  'phone_number' => $phone,
];

// Actualiza los datos del usuario en la base de datos
if ($user->actualizarUsuario($nuevosDatos)) {
  echo "<script>alert('Usuario actualizado exitosamente'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=$dni';</script>";
} else {
  echo "<script>alert('Ocurrió un error al actualizar el usuario'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=$dni';</script>";
}

?>
