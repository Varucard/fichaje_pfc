<?php

require_once 'conexion_model.php';

class User {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }

  public function getUserByDNI(string $dni) {
    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->query("SELECT * FROM `users` WHERE `dni` = $dni");
  
      // Obtengo el Usuario
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  public function getUserByRFID(string $rfid) {
    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->query("SELECT * FROM `users` WHERE `rfid` = $rfid");
  
      // Obtengo el Usuario
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  public function getUserByName(string $name) {
    try {
      $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `name` LIKE :name");
      $name = "%" . $name . "%"; // Agrega comodines para la búsqueda
      $stmt->bindParam(':name', $name, PDO::PARAM_STR);
      $stmt->execute();
  
      // Obtengo el Usuario
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  public function getUsers() {
    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->query("SELECT * FROM `users`");
  
      // Obtengo los Usuarios
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  public function cargarUsuario(array $user) {
    $rfid = $user[0];
    $dni = $user[1];
    $name = $user[2];
    $surname = $user[3] ?? NULL;
    $birth_day = $user[4] ?? NULL;
    $email = $user[5] ?? NULL;
    $phone_number = $user[6] ?? NULL;

    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->prepare("INSERT INTO `users`(`rfid`, `dni`, `name`, `surname`, `birth_day`, `email`, `phone_number`) VALUES (:rfid, :dni, :name, :surname, :birth_day, :email, :phone_number)");

      // Enlazar los parámetros
      $stmt->bindParam(':rfid', $rfid, PDO::PARAM_STR);
      $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
      $stmt->bindParam(':name', $name, PDO::PARAM_STR);
      $stmt->bindParam(':surname', $surname, PDO::PARAM_STR);
      $stmt->bindParam(':birth_day', $birth_day, PDO::PARAM_STR);
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
