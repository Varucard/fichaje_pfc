<?php
require_once '../models/uid_model.php';

header('Content-Type: application/json');

$uidModel = new UID();
$uidData = $uidModel->getLastUID();

if ($uidData) {
    $uid = $uidData;
    // Enviar el UID como respuesta JSON
    echo json_encode(['uid' => $uid]);

    // Eliminar el UID de la base de datos
    $uidModel->deleteUID($uid);
} else {
    echo json_encode(['uid' => null]);
}
?>
