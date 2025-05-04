<?php

require_once 'conexion_model.php';

class User {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }

  // Trae un usuario
  public function getUserByID(string $id_user) {
    try {
      $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `id_user` = :id_user");
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }


  // Trae un usuario
  public function getUserByDNI(string $dni) {
    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `dni` = :dni");
      $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
      $stmt->execute();
  
      // Obtengo el Usuario
      return $stmt->fetch(PDO::FETCH_ASSOC);
  
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Trae un Usuario con el RFID siempre y cuando este usuario este activo
  public function getUserByRFID(string $rfid) {
    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `rfid` = :rfid AND `asset` = 1");
      $stmt->bindParam(':rfid', $rfid, PDO::PARAM_STR);
      $stmt->execute();
      
      // Obtener el Usuario
      return $stmt->fetch(PDO::FETCH_ASSOC);
        
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Trae el registro mas actual de un Usuario que haya tenido el RFID registrado
  public function getUltimoRegistroPorRFID(string $rfid) {
    try {
      $stmt = $this->pdo->prepare("
          SELECT * FROM `users`
          WHERE `rfid` = :rfid
          ORDER BY `id_user` DESC
          LIMIT 1
      ");
      $stmt->bindParam(':rfid', $rfid, PDO::PARAM_STR);
      $stmt->execute();

      return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }


  // Trae un usuario
  public function getUserByName(string $name) {
    try {
      $stmt = $this->pdo->prepare("SELECT * FROM `users` WHERE `user_name` LIKE :name");
      $name = "%" . $name . "%"; // Agrega comodines para la búsqueda
      $stmt->bindParam(':user_name', $name, PDO::PARAM_STR);
      $stmt->execute();
  
      // Obtengo el Usuario
      return $stmt->fetchAll(PDO::FETCH_ASSOC);
  
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Trae todos los usuarios sin discriminación de tipo
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

  // Registra un nuevo Usuario
  public function cargarUsuario(array $user) {
    $rfid = $user[0];
    $dni = $user[1];
    $name = $user[2];
    $surname = ($user[3] == '') ? NULL : $user[3];
    $birth_day = ($user[4] == '') ? NULL : $user[4];
    $email = ($user[5] == '') ? NULL : $user[5];
    $phone_number = ($user[6] == '') ? NULL : $user[6];
    $type_user = $user[7] ?? 2;

    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->prepare("INSERT INTO `users`(`rfid`, `dni`, `user_name`, `user_surname`, `birth_day`, `email`, `phone_number`, `type_user`) VALUES (:rfid, :dni, :user_name, :user_surname, :birth_day, :email, :phone_number, :type_user)");

      // Enlazar los parámetros
      $stmt->bindParam(':rfid', $rfid, PDO::PARAM_STR);
      $stmt->bindParam(':dni', $dni, PDO::PARAM_STR);
      $stmt->bindParam(':user_name', $name, PDO::PARAM_STR);
      $stmt->bindParam(':user_surname', $surname, PDO::PARAM_STR);
      $stmt->bindParam(':birth_day', $birth_day, PDO::PARAM_STR);
      $stmt->bindParam(':email', $email, PDO::PARAM_STR);
      $stmt->bindParam(':phone_number', $phone_number, PDO::PARAM_STR);
      $stmt->bindParam(':type_user', $type_user, PDO::PARAM_STR);

      // Ejecutar la consulta
      return $stmt->execute();

    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Actualiza un Usuario
  public function actualizarUsuario($datos) {
    try {
        $surname     = !empty($datos['surname'])     ? $datos['surname']     : null;
        $birth_day   = !empty($datos['birth_day'])   ? $datos['birth_day']   : null;
        $email       = !empty($datos['email'])       ? $datos['email']       : null;
        $phoneNumber = !empty($datos['phone_number'])? $datos['phone_number']: null;

        $stmt = $this->pdo->prepare("UPDATE users SET user_name = :name, user_surname = :surname, birth_day = :birth_day, rfid = :rfid, dni = :dni, email = :email, phone_number = :phone, type_user = :type_user WHERE id_user = :id");
        $stmt->bindParam(':name',       $datos['name'],        PDO::PARAM_STR);
        $stmt->bindParam(':surname',    $surname,              PDO::PARAM_STR);
        $stmt->bindParam(':birth_day',  $birth_day,            PDO::PARAM_STR);
        $stmt->bindParam(':rfid',       $datos['rfid'],        PDO::PARAM_STR);
        $stmt->bindParam(':dni',        $datos['dni'],         PDO::PARAM_STR);
        $stmt->bindParam(':email',      $email,                PDO::PARAM_STR);
        $stmt->bindParam(':phone',      $phoneNumber,          PDO::PARAM_STR);
        $stmt->bindParam(':type_user',  $datos['type_user'],   PDO::PARAM_STR);
        $stmt->bindParam(':id',         $datos['id'],          PDO::PARAM_INT);

        return $stmt->execute();
    } catch (PDOException $e) {
        // echo "Error en la consulta: " . $e->getMessage();
        return false;
    }
  }

  // Elimmina el llavero del usuario Alumno
  public function desetearRFID($id_user) {
    try {
      $stmt = $this->pdo->prepare("UPDATE users SET rfid = 'SIN LLAVERO' WHERE id_user = :id_user");
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Reactiva usuarios
  public function activarUsuario($id_user) {
    try {
      $stmt = $this->pdo->prepare("UPDATE users SET asset = 1 WHERE id_user = :id_user");
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_STR);
      return $stmt->execute();
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Desactiva usuarios
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

  // Trae usuarios que cumplan años
  public function getUsersFestejados($date) {
    try {
        // Preparar la consulta SQL para obtener usuarios con cumpleaños en la fecha dada (mes y día)
        $stmt = $this->pdo->prepare("
            SELECT id_user, user_name, user_surname
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
        // echo "Error en la consulta: " . $e->getMessage();
        return [];
    }
  }

  // Trae usuario según su rol
  public function getUsersByRole($type_user) {
    try {
      // Preparar la consulta SQL para seleccionar usuarios por rol
      $sql = 'SELECT * FROM users WHERE type_user = :type_user';
      $stmt = $this->pdo->prepare($sql);

      // Vincular el parámetro
      $stmt->bindParam(':type_user', $type_user, PDO::PARAM_STR);

      // Ejecutar la consulta
      $stmt->execute();

      // Obtener todos los registros que coincidan con el rol
      return $stmt->fetchAll(PDO::FETCH_OBJ);

    } catch (PDOException $e) {
      // Manejar cualquier excepción de PDO
      // echo 'Error: ' . $e->getMessage();
      return false;
    }
}

}

?>
