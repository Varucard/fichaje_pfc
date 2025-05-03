<?php
header('Content-Type: application/json');

// Obtener UID enviado por el Arduino
$uid = isset($_GET['uid']) ? $_GET['uid'] : null;

if (!$uid) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'UID no proporcionado'
    ]);
    exit;
}

// -------------------------------------------------------------------
// Acá va tu lógica para consultar la base de datos o archivos, etc.
// Según el UID, tenés que obtener estos campos:
// - estado: "activo", "moroso", "inactivo", o cualquier otro
// - nombre
// - apellido
// - mensaje (opcional, por ejemplo: "Buen día" o lo que quieras mostrar)
// -------------------------------------------------------------------

// EJEMPLO SIMULADO (reemplazar esto con tu lógica real):
switch ($uid) {
    case "1234567890":
        $estado = "activo";
        $nombre = "Juan";
        $apellido = "Pérez";
        $mensaje = "Buen día!";
        break;
    case "9876543210":
        $estado = "moroso";
        $nombre = "";
        $apellido = "";
        $mensaje = "";
        break;
    case "5555555555":
        $estado = "inactivo";
        $nombre = "";
        $apellido = "";
        $mensaje = "";
        break;
    default:
        $estado = "desconocido";
        $nombre = "";
        $apellido = "";
        $mensaje = "";
        break;
}

// Respuesta al Arduino
echo json_encode([
    'status' => 'ok',
    'estado' => $estado,
    'nombre' => $nombre,
    'apellido' => $apellido,
    'mensaje' => $mensaje
]);
