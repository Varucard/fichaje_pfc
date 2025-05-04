<?php
header('Content-Type: application/json');

require_once '../models/uid_model.php';
$uidModel = new UID();

// Obtener todos los UIDs desconocidos
$uidsDesconocidos = $uidModel->getAllUIDs();

$uidModel->deleteUID();

// Responder con los UIDs desconocidos
echo json_encode([
  'status' => 'ok',
  'rfid_desconocidos' => $uidsDesconocidos
]);
?>
