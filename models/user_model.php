<?php

require_once 'conexion_model.php';

class User {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }

  public function getUserByID(string $id_user) {
    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->query("SELECT * FROM `users` WHERE `id_user` = $id_user");
  
      // Obtengo el Usuario
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
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
      $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `rfid` = :rfid");
      $stmt->bindParam(':rfid', $rfid, PDO::PARAM_STR);
      $stmt->execute();
      
      // Obtener el Usuario
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

  public function actualizarUsuario($datos) {
    try {
        $stmt = $this->pdo->prepare("UPDATE users SET name = :name, surname = :surname, birth_day = :birth_day, rfid = :rfid, dni = :dni, email = :email, phone_number = :phone WHERE id_user = :id");
        $stmt->bindParam(':name', $datos['name'], PDO::PARAM_STR);
        $stmt->bindParam(':surname', $datos['surname'], PDO::PARAM_STR);
        $stmt->bindParam(':birth_day', $datos['birth_day'], PDO::PARAM_STR);
        $stmt->bindParam(':rfid', $datos['rfid'], PDO::PARAM_STR);
        $stmt->bindParam(':dni', $datos['dni'], PDO::PARAM_STR);
        $stmt->bindParam(':email', $datos['email'], PDO::PARAM_STR);
        $stmt->bindParam(':phone', $datos['phone_number'], PDO::PARAM_STR);
        $stmt->bindParam(':id', $datos['id'], PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
  }

  public function desetearRFID($dni) {
    try {
      $stmt = $this->pdo->prepare("UPDATE users SET rfid = 'SIN LLAVERO' WHERE dni = :dni");
      $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  public function activarUsuario($dni) {
    try {
      $stmt = $this->pdo->prepare("UPDATE users SET asset = 1 WHERE dni = :dni");
      $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  public function desactivarUsuario($dni) {
    try {
      $stmt = $this->pdo->prepare("UPDATE users SET asset = 0 WHERE dni = :dni");
      $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  public function getUsersFestejados($date) {
    try {
        // Preparar la consulta SQL para obtener usuarios con cumpleaños en la fecha dada (mes y día)
        $stmt = $this->pdo->prepare("
            SELECT id_user, name, surname
            FROM users
            WHERE DATE_FORMAT(birth_day, '%m-%d') = DATE_FORMAT(?, '%m-%d')
        ");
        
        // Establecer el parámetro de la consulta
        $stmt->execute([$date]);
        
        // Obtener el resultado
        $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Devolver el array de usuarios
        return $usuarios;
    } catch (PDOException $e) {
        // Manejar la excepción
        echo "Error en la consulta: " . $e->getMessage();
        return [];
    }
  }

}

?>
