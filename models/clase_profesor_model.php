<?php

require_once 'conexion_model.php';

class ClaseProfesor {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }  

  public function createClaseProfesor($data) {
    try {
      // Preparar la consulta SQL de inserción
      $sql = 'INSERT INTO classes (id_user, id_class) VALUES (:id_user, :id_class)';
      $stmt = $this->pdo->prepare($sql);

      // Vincular los parámetros con los valores del arreglo $data
      $stmt->bindParam(':id_user', $data['id_user'], PDO::PARAM_STR);
      $stmt->bindParam(':id_class', $data['id_class'], PDO::PARAM_STR);

      // Ejecutar la consulta
      return $stmt->execute();
    } catch (PDOException $e) {
      // Manejar cualquier excepción de PDO
      echo 'Error: ' . $e->getMessage();
      return false;
    }
  }

  public function getClasesProfesor() {
    try {
      $sql = 'SELECT * FROM teacher_class';
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
      echo 'Error: ' . $e->getMessage();
      return [];
    }
  }

  public function getClaseProfesorByIdProfesor($idUser) {
    try {
      $sql = 'SELECT * FROM teacher_class WHERE id_user = :id_user';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':id_user', $idUser, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
      echo 'Error: ' . $e->getMessage();
      return null;
    }
  }

  public function getClaseProfesorByIdClass($idClass) {
    try {
      $sql = 'SELECT * FROM teacher_class WHERE id_class = :id_class';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':id_class', $idClass, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetch(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
      echo 'Error: ' . $e->getMessage();
      return null;
    }
  }

  public function deleteClaseProfesorById($id) {
    try {
      $sql = 'DELETE FROM teacher_class WHERE id_teacher_class = :id_teacher_class';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':id_teacher_class', $id, PDO::PARAM_INT);
      return $stmt->execute();
    } catch (PDOException $e) {
      echo 'Error: ' . $e->getMessage();
      return false;
    }
  }
}

?>
