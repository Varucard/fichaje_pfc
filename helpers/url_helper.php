<?php

require_once dirname(__DIR__) . '/config/config.php';

// Redirige a la página especificada
function redirect($page) {
	header('Location:' . getenv('URLROOT') . '/' . $page);
	exit();
}

// Obtiene la URL actual
function currentUrl() {
	$url = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
	return $url;
}

// Verifica si un parámetro existe en la URL
function getParam($param) {
	return isset($_GET[$param]) ? $_GET[$param] : null;
}

function checkSesion() {
	if (!isset($_SESSION['login'])) {
		redirect('views/login_view.php');
		exit;
	}
}
?>
