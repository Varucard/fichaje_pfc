<?php
require_once '../helpers/url_helper.php';
session_start();
checkSesion();
?>
<!DOCTYPE html>
<html lang="es">
	<head>
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<title>Panel del Administrador - Palillo Fight Club</title>
		<link rel="shortcut icon" href="../public/img/ico_logo.png">
		<link rel="stylesheet" href="../public/css/water.css">
		<link rel="stylesheet" href="../public/css/estilos.css">
		<link rel="stylesheet" href="../public/css/dashboard.css">
		<link rel="stylesheet" href="../public/css/festejados.css">
		<link rel="stylesheet" href="../public/css/dashboard.css"> <!-- Nuevo archivo CSS -->
	</head>
	
	<body>
		<header class="header">
				<img src="../public/img/logo.png" alt="Logo Palillo Fight Club">
				<h1>Palillo Fight Club</h1>
		</header>

		<main class="container">
			<h2 class="welcome">¡Bienvenido Administrador!</h2>

			<section class="search-container">
				<i class="fas fa-search search-icon"></i>
				<input type="text" id="busqueda_fichaje" placeholder="Buscar Ingreso">
			</section>
			<section class="search-container">
				<i class="fas fa-search search-icon"></i>
				<input type="text" id="busqueda_usuario" placeholder="Buscar Cliente/Profesor">
			</section>
			<section class="search-container">
				<i class="fas fa-search search-icon"></i>
				<input type="text" id="busqueda_clase" placeholder="Buscar Clase">
			</section>

			<div class="button-group">
				<button id="fichaje_manual">
					<i class="fas fa-clock"></i> Registrar Fichada
				</button>

				<button onclick="window.location.href='cargar_usuario_view.php'" id="cargar_usuario">
					<i class="fas fa-user-plus"></i> Agregar Cliente/Profesor
				</button>

				<button id="cargar_pago_manual_usuario">
					<i class="fas fa-wallet"></i> Abonar Clase
				</button>

				<button onclick="window.location.href='../controllers/cargar_clase_controller.php'">
					<i class="fas fa-chalkboard-teacher"></i> Agregar Clase
				</button>
				<button onclick="window.location.href='../controllers/lista_clases_controller.php'">
					<i class="fas fa-chalkboard-teacher"></i> Clases
				</button>

				<button onclick="window.location.href='../controllers/lista_clientes_controller.php'">
					<i class="fas fa-users"></i> Clientes
				</button>

				<button onclick="window.location.href='../controllers/lista_profesores_controller.php'">
					<i class="fas fa-user-graduate"></i> Profesores
				</button>

				<button onclick="window.location.href='fichajes_view.php'">
					<i class="fas fa-clipboard-check"></i> Últimos Ingresos
				</button>
			</div>

			<div class="button-group-danger">
					<button class="danger" onclick="window.location.href='../config/reiniciar_arduino.php'">
							<i class="fas fa-sync-alt"></i> Reiniciar Arduino
					</button>
					<form action="../controllers/auth_controller.php?action=logout" method="POST">
							<button class="danger" type="submit">
									<i class="fas fa-sign-out-alt"></i> Cerrar Sesión
							</button>
					</form>
			</div>

			<div id="cumpleanosModal" class="modal">
				<div class="modal-content">
					<span class="close">×</span>
					<p id="cumpleanosTexto"></p>
				</div>
			</div>
		</main>

		<script src="../public/js/busqueda_fichaje.js"></script>
		<script src="../public/js/busqueda_usuario.js"></script>
		<script src="../public/js/busqueda_clase.js"></script>
		<script src="../public/js/fichaje_manual.js"></script>
		<script src="../public/js/festejados.js"></script>
		<script src="../public/js/icons.js"></script>
		<script src="../public/js/pago_manual_user.js"></script>
	</body>
</html>