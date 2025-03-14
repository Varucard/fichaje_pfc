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
		<!-- <link rel="stylesheet" href="../public/css/estilos.css"> -->
		<link rel="stylesheet" href="../public/css/festejados.css">
		<link rel="stylesheet" href="../public/css/dashboard.css"> <!-- Nuevo archivo CSS -->
	</head>
	
	<body class="fondo">

		<header class="header">
			<div class="title-container">
				<i class="fa-solid fa-bars hamburger-icon"></i>
				<div class="home" onclick="window.location.href='dashboard_view.php'">
					<img class="header-img" src="../public/img/logo_sin_blanco.png" alt="Logo Palillo Fight Club">
					<h1 class="header-title">PALILLO FIGHT CLUB</h1>
				</div>
			</div>

			<div class="header-button-group">
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
		</header>
		

		<main class="container">
			<aside class="mobile-menu d-none">
				<div class="mobile-title">
					<div class="home">
						<i class="fa-solid fa-bars hamburger-icon"></i>
						<img class="header-img" src="../public/img/logo_sin_blanco.png" alt="Logo Palillo Fight Club">
					</div>
				</div>

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
			</aside>

		
			<div>
				<h2 class="welcome">¡Bienvenido Administrador!</h2>
					
				<div class="button-group">
					<button id="fichaje_manual">
						<i class="fas fa-clock"></i> Registrar Fichada
					</button>
					
					<button id="cargar_pago_manual_usuario">
						<i class="fas fa-wallet"></i> Abonar Clase
					</button>
	
					<button onclick="window.location.href='cargar_usuario_view.php'" id="cargar_usuario">
						<i class="fas fa-user-plus"></i> Agregar cliente o profesor
					</button>
		
					<button onclick="window.location.href='../controllers/cargar_clase_controller.php'">
						<i class="fas fa-chalkboard-teacher"></i> Agregar Clase
					</button>
				</div>

				<div class="search-container">
					<section class="search">
						<i class="fas fa-search search-icon"></i>
						<input type="text" id="busqueda_fichaje" placeholder="Buscar Ingreso">
					</section>
				</div>
	
	
				<div class="button-group-danger">
					<button class="danger" onclick="window.location.href='../config/reiniciar_arduino.php'">
							<i class="fas fa-sync-alt"></i> <p>Reiniciar Arduino</p>
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
			</div>
		</main>

		<script src="../public/js/busqueda_fichaje.js"></script>
		<!-- <script src="../public/js/busqueda_usuario.js"></script>
		<script src="../public/js/busqueda_clase.js"></script> -->
		<script src="../public/js/animaciones_dashboard.js"></script>
		<script src="../public/js/fichaje_manual.js"></script>
		<script src="../public/js/festejados.js"></script>
		<script src="../public/js/icons.js"></script>
		<script src="../public/js/pago_manual_user.js"></script>
	</body>
</html>