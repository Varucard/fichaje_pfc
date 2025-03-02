<?php

require_once 'conexion_model.php';

class Clase {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }  

  // Crea una clase
  public function createClase($data) {
    try {
      // Preparar la consulta SQL de inserción
      $sql = 'INSERT INTO classes (name_class, price_class) VALUES (:name_class, :price_class)';
      $stmt = $this->pdo->prepare($sql);

      // Vincular los parámetros con los valores del arreglo $data
      $stmt->bindParam(':name_class', $data['name_class'], PDO::PARAM_STR);
      $stmt->bindParam(':price_class', $data['price_class'], PDO::PARAM_STR);

      // Ejecutar la consulta
      return $stmt->execute();
    } catch (PDOException $e) {
      // echo 'Error: ' . $e->getMessage();
      return false;
    }
  }

  // Actualiza una clase
  public function updateClase($data) {
    try {
      $stmt = $this->pdo->prepare("UPDATE classes SET name_class = :name_class, price_class = :price_class WHERE id_class = :id_class");
      $stmt->bindParam(':name_class', $data['name_class'], PDO::PARAM_STR);
      $stmt->bindParam(':price_class', $data['price_class'], PDO::PARAM_STR);
      $stmt->bindParam(':id_class', $data['id_class'], PDO::PARAM_STR);

      return $stmt->execute();
    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Obtener todas las clases
  public function getClases() {
    try {
      $sql = 'SELECT * FROM classes';
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
      // echo 'Error: ' . $e->getMessage();
      return [];
    }
  }

  // Obtener clases por su nombre
  public function getClaseByNameClase($nameClase) {
    try {
      $sql = 'SELECT * FROM classes WHERE name_class = :name_class';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':name_class', $nameClase, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
      // echo 'Error: ' . $e->getMessage();
      return false;
    }
  }

  // Obtiene clases por su nombre
  public function getClasesByNameClase($nameClase) {
    try {
        $sql = 'SELECT * FROM classes WHERE name_class LIKE :name_class';
        $stmt = $this->pdo->prepare($sql);
        $nameClase = "%$nameClase%"; // Agregar comodines para búsqueda parcial
        $stmt->bindParam(':name_class', $nameClase, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_OBJ); // Obtener todos los resultados
    } catch (PDOException $e) {
        // echo 'Error: ' . $e->getMessage();
        return false;
    }
  }


  // Obtener una clase por su ID
  public function getClaseById($id) {
    try {
      $sql = 'SELECT * FROM classes WHERE id_class = :id_class';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':id_class', $id, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
      // echo 'Error: ' . $e->getMessage();
      return false;
    }
  }

  // Eliminar una clase por su ID
  public function deleteClaseById($id, $eliminarMatriculaciones = false) {
    try {
        $this->pdo->beginTransaction(); // Iniciar transacción

        if ($eliminarMatriculaciones) {
            // Eliminar las matriculaciones en user_class
            $sqlUserClass = 'DELETE FROM user_class WHERE id_class = :id_class';
            $stmtUserClass = $this->pdo->prepare($sqlUserClass);
            $stmtUserClass->bindParam(':id_class', $id, PDO::PARAM_INT);
            $stmtUserClass->execute();

            // Eliminar las matriculaciones en teacher_class
            $sqlTeacherClass = 'DELETE FROM teacher_class WHERE id_class = :id_class';
            $stmtTeacherClass = $this->pdo->prepare($sqlTeacherClass);
            $stmtTeacherClass->bindParam(':id_class', $id, PDO::PARAM_INT);
            $stmtTeacherClass->execute();
        }

        // Ahora eliminar la clase en classes
        $sqlClase = 'DELETE FROM classes WHERE id_class = :id_class';
        $stmtClase = $this->pdo->prepare($sqlClase);
        $stmtClase->bindParam(':id_class', $id, PDO::PARAM_INT);
        $stmtClase->execute();

        $this->pdo->commit(); // Confirmar cambios
        return true;

    } catch (PDOException $e) {
        $this->pdo->rollBack(); // Revertir cambios si hay un error
        // echo 'Error: ' . $e->getMessage(); // Para depuración, si lo necesitas
        return false;
    }
  }

}

?>
