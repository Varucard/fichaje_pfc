<?php
// URL de tu Arduino con el endpoint de reinicio
$url = 'http://' . getenv('IP_ARDUINO') . '/reiniciar';

// Usar cURL para enviar la solicitud GET en segundo plano
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 1); // Timeout muy corto para no esperar mucho tiempo
curl_exec($ch);
curl_close($ch);

// Mostrar alerta y redirigir inmediatamente
echo 
	"<script>
    alert('Reiniciando Arduino. Por favor aguarde...');
		window.location.href = '../views/dashboard_view.php';
	</script>";
?>
