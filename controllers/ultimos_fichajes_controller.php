<?php
require_once '../models/fichaje_model.php';

$fichajesModel = new Fichajes();
$ultimosFichajes = $fichajesModel->getUltimosFichajes();

$fichajesConPago = [];
foreach ($ultimosFichajes as $fichaje) {
  $fechaPago = $fichajesModel->getUltimaFechaPago($fichaje['id_user']);
  $fichaje['date_of_renovation'] = $fechaPago;

  // Calcular la diferencia de días entre la fecha de pago y la fecha actual
  if ($fechaPago) {
    $fechaActual = new DateTime();
    $fechaPagoDateTime = new DateTime($fechaPago);
    $diferenciaDias = $fechaPagoDateTime->diff($fechaActual)->days;
    $estaCerca = ($diferenciaDias <= 5);
    $yaPaso = ($fechaPagoDateTime < $fechaActual && $diferenciaDias <= 5);
    $fichaje['pago_cerca'] = $estaCerca || $yaPaso;
  } else {
    $fichaje['pago_cerca'] = false;
  }

  $fichajesConPago[] = $fichaje;
}

// Limitar los fichajes a los 10 registros más recientes e invertir el orden
$fichajesConPago = array_slice($fichajesConPago, 0, 10);
// $fichajesConPago = array_reverse($fichajesConPago);

echo json_encode($fichajesConPago);
?>
