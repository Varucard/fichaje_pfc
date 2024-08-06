<?php

function crearArchivoFechaArduino() {
  // Obtener la fecha y hora actual
  date_default_timezone_set('America/Argentina/Buenos_Aires');
  $date = date('Y-m-d');

  // Escribir la fecha y hora en un archivo
  file_put_contents('../config/fecha_arduino.txt', $date);
}

?>
