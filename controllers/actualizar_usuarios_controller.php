<?php

require_once '../models/user_model.php';

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
  echo "<script>alert('Usuario actualizado exitosamente'); window.location.href = '../index.php';</script>";
} else {
  echo "<script>alert('Ocurrió un error al actualizar el usuario'); window.location.href = '../views/detalle_usuario_view.php?dni=$dni';</script>";
}
?>


?>