<?php
require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';
require_once '../helpers/session_helper.php';

class AuthController {
  private $userModel;

  public function __construct() {
    $this->userModel = new User;
  }

  // Método para iniciar sesión
  public function login() {
    // Recoger los datos del formulario
    $dni = trim($_POST['dni']);
    $password = trim($_POST['password']);

    // Validar que no estén vacíos
    if(empty($dni) || empty($password)) {
      flash('login_error', 'Por favor, complete todos los campos');
      redirect('../views/login_view.php');
    }

    // Intentar iniciar sesión
    if($this->userModel->login($dni, $password)) {
      // Inicio de sesión exitoso
      redirect('../views/fichaje_view.php');
    } else {
      // Fallo en el inicio de sesión
      flash('login_error', 'Credenciales incorrectas');
      redirect('../views/login_view.php');
    }
  }
}

// Comprobar si se envió una acción y llamar al método correspondiente
if (isset($_GET['action']) && method_exists('AuthController', $_GET['action'])) {
  $action = $_GET['action'];
  $controller = new AuthController();
  $controller->{$action}();
} else {
  die('Acción no válida.');
}
