<?php
session_start();

require_once '../models/clase_model.php';
require_once '../models/user_model.php';
require_once '../models/clase_profesor_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$clase = new Clase();
$user = new User();
$claseProfesor = new ClaseProfesor();

// Valido que lo indispensable me llegue (Nombre de la clase y precio)
if (empty($_POST['nombre_clase']) || empty($_POST['precio'])) {
  echo "<script>alert('Por favor, ingrese el nombre de la clase y precio'); window.location.href = '../controllers/cargar_clase_controller.php';</script>";
  exit; 
} 

// Captura y normaliza los datos del formulario
$nombreClase = ucwords(strtolower(trim($_POST['nombre_clase'])));
$precio = number_format(floatval($_POST['precio']), 0, ',', '');  // Normaliza el precio
$profesores = $_POST['profesores'] ?? [];

// Verifico si la clase existe
if ($clase->getClaseByNameClase($nombreClase)) {
  echo "<script>alert('La clase ya se encuentra creada'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . htmlspecialchars($_POST['dni']) . "';</script>";
  exit; 
}

// Creo la clase
$nuevaClase = $clase->createClase(['name_class' => $nombreClase, 'price_class' => $precio]);

if (!$nuevaClase) {
  echo "<script>alert('Ocurrió un error al guardar la clase'); window.location.href = '../controllers/cargar_clase_controller.php';</script>";
  exit;
}

// Asignar profesores a la clase, si hay profesores seleccionados
$erroresProfesores = [];
if (!empty($profesores)) {
  $nuevaClaseId = $clase->getClaseByNameClase($nombreClase)->id_class;

  foreach ($profesores as $profesorId) {
    $profesor = $user->getUserByID($profesorId);

    if ($profesor[0]['type_user'] != 3) {
      $erroresProfesores[] = "El usuario con ID $profesorId no es un profesor.";
    } else {
      $claseProfesor->createClaseProfesor(['id_class' => $nuevaClaseId, 'id_user' => $profesor[0]['id_user']]);
    }
  }
}

// Mostrar mensaje final al usuario
if (empty($erroresProfesores)) {
  echo "<script>alert('Clase creada con los profesores solicitados'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($nuevaClaseId) . "';</script>";
} else {
  echo "<script>alert('Clase creada, pero algunos profesores no fueron asignados: " . implode(', ', $erroresProfesores) . "'); window.location.href = '../controllers/cargar_clase_controller.php';</script>";
}
?>
