<?php
  session_start();

  require_once '../models/pago_model.php';
  require_once '../models/user_model.php';
  require_once '../helpers/url_helper.php';

  $user = new User();

  function nuevo_pago_manual($id_user, $fecha_pago_manual) {
    $pago = new Pagos();

    // Convertir la fecha de pago manual a DateTime
    $fecha_pago_manual_dt = DateTime::createFromFormat('Y-m-d', $fecha_pago_manual);

    if ($fecha_pago_manual_dt === false) {
      return false; // Fecha inválida
    }

    // Calcular la fecha 30 días después
    $fecha_30_dias_mas_dt = clone $fecha_pago_manual_dt;
    $fecha_30_dias_mas_dt->modify('+30 days');

    $fecha_pago_manual_str = $fecha_pago_manual_dt->format('Y-m-d H:i:s');
    $fecha_30_dias_mas_str = $fecha_30_dias_mas_dt->format('Y-m-d H:i:s');

    $nuevo_pago = [
      $id_user,
      $fecha_pago_manual_str,
      $fecha_30_dias_mas_str
    ];

    return $pago->cargarPago($nuevo_pago);
  }

  $id_user = isset($_GET['id_user']) ? $_GET['id_user'] : '';
  $dni_user = isset($_GET['dni']) ? $_GET['dni'] : '';
  $fecha_pago_manual = isset($_GET['fecha_pago_manual']) ? $_GET['fecha_pago_manual'] : '';

  $usuario = $user->getUserByID($id_user);

  // Si el usuario no aparece con id lo busco con el DNI, una seguridad para user el mismo controladora en diferentes casos
  if (!$usuario) {
    $usuario = $user->getUserByDNI($dni_user);
    $id_user = $usuario[0]['id_user'];
  }

  if ($id_user && $fecha_pago_manual && $usuario && nuevo_pago_manual($id_user, $fecha_pago_manual)) {
    echo "<script>alert('Pago registrado exitosamente'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . urlencode($usuario[0]['dni']) . "';</script>";
  } else {
    echo "<script>alert('Ocurrió un error al registrar el pago'); window.location.href = '../controllers/detalle_usuario_controller.php?dni=" . urlencode($usuario[0]['dni']) . "';</script>";
  }
?>
