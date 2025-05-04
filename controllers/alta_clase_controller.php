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
$dataClase = $clase->getClaseByNameClase($nombreClase);

// Verifico si la clase existe
if ($dataClase) {
  echo "<script>alert('La clase ya se encuentra creada'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($dataClase->id_class) . "';</script>";
  exit; 
}

// Creo la clase
$nuevaClase = $clase->createClase(['name_class' => $nombreClase, 'price_class' => $precio]);

if (!$nuevaClase) {
  echo "<script>alert('Ocurrió un error al crear la clase'); window.location.href = '../controllers/cargar_clase_controller.php';</script>";
  exit;
}

// Asignar profesores a la clase, si hay profesores seleccionados
$erroresProfesores = [];
if (!empty($profesores)) {
  $nuevaClase = $clase->getClaseByNameClase($nombreClase);

  foreach ($profesores as $profesorId) {
    $profesor = $user->getUserByID($profesorId);

    if ($profesor['type_user'] == 1) {
      $error = $claseProfesor->createClaseProfesor($profesor['id_user'], $nuevaClase->id_class);
    } else {
      $erroresProfesores[] = "El usuario con ID $profesorId no es un profesor.";
    }
  }
}

// TODO: Poder agregar Usuarios al momento de crear una clase

// Mostrar mensaje final al usuario
if (empty($erroresProfesores)) {
  echo "<script>alert('Clase creada con los profesores solicitados'); window.location.href = '../controllers/detalle_clase_controller.php?id_class=" . htmlspecialchars($nuevaClase->id_class) . "';</script>";
} else {
  echo "<script>alert('Clase creada, pero algunos profesores no fueron asignados: " . implode(', ', $erroresProfesores) . "'); window.location.href = '../controllers/cargar_clase_controller.php';</script>";
}
?>
