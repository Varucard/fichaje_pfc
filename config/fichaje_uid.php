<?php

require_once '../models/fichaje_model.php';
require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';

function registrarFichajeManual($dni) {
  $user = new User();
  $fichaje = new Fichajes();

  $usuario = $user->getUserByDNI($dni);
  if (!$usuario) {
    return ['status' => false, 'message' => 'Usuario no encontrado'];
  }

  date_default_timezone_set('America/Argentina/Buenos_Aires');
  $fecha_actual = new DateTime();
  $fecha_fichada = $fecha_actual->format('Y-m-d H:i:s');

  if ($fichaje->guardarFichada($usuario['id_user'], $fecha_fichada)) {
    return ['status' => true, 'message' => 'Fichada registrada'];
  } else {
    return ['status' => false, 'message' => 'Error al guardar fichada'];
  }
}
?>
