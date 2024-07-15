<?php

require_once 'conexion_model.php';

class User {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }

  public function getUser(string $dni) {
    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->query("SELECT * FROM `users` WHERE `dni` = $dni");
  
      // Obtengo el Usuario
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    } catch (PDOException $e) {
      echo "Error en la consulta: " . $e->getMessage();
    }
  }

  public function getUsers() {
    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->query("SELECT * FROM `users`");
  
      // Obtengo los Usuarios
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    } catch (PDOException $e) {
      echo "Error en la consulta: " . $e->getMessage();
    }
  }

  public function cargarUsuario(array $user) {
    $rfid = $user[0];
    $dni = $user[1];
    $name = $user[2];
    $surname = $user[3] ?? NULL;
    $email = $user[4] ?? NULL;
    $phone_number = $user[5] ?? NULL;

    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->prepare("INSERT INTO `users`(`rfid`, `dni`, `name`, `surname`, `email`, `phone_number`) VALUES (:rfid, :dni, :name, :surname, :email, :phone_number)");

      // Enlazar los parámetros
      $stmt->bindParam(':rfid', $rfid, PDO::PARAM_STR);
      $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
      $stmt->bindParam(':name', $name, PDO::PARAM_STR);
      $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);
      $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);

      // Ejecutar la consulta
      return $stmt->execute();

    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
}


}

?>
