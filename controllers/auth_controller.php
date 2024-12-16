<?php
require_once '../models/user_model.php';
require_once '../helpers/url_helper.php';
require_once '../helpers/session_helper.php';

class AuthController {
  private $userModel;

  public function __construct() {
    $this->userModel = new User;
  }

  public function login() {
    $dni = trim($_POST['dni']);
    $password = trim($_POST['password']);

    // Valido que no estén vacíos
    if(empty($dni) || empty($password)) {
      flash('login_error', 'Por favor, complete todos los campos');
      redirect('views/login_view.php');
    }

    if($this->userModel->login($dni, $password)) {
      $_SESSION['login'] = true;
      redirect('views/dashboard_view.php');
    } else {
      flash('login_error', 'Credenciales incorrectas');
      redirect('views/login_view.php');
    }
  }

  // Destruir la sesión
  public function logout() {
    session_unset();
    session_destroy();
    redirect('views/login_view.php');
    exit;
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
