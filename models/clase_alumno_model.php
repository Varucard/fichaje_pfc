<?php

require_once 'conexion_model.php';

class ClaseAlumno {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }  

  // Crea una matriculación de clase de un Usuario Alumno
  public function createClaseAlumno($id_user, $id_class) {
    try {
      // Preparar la consulta SQL de inserción
      $sql = 'INSERT INTO user_class (id_user, id_class) VALUES (:id_user, :id_class)';
      $stmt = $this->pdo->prepare($sql);

      // Vincular los parámetros con los valores del arreglo $data
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_STR);
      $stmt->bindParam(':id_class', $id_class, PDO::PARAM_STR);

      // Ejecutar la consulta
      return $stmt->execute();
    } catch (PDOException $e) {
      // return 'Error: ' . $e->getMessage();
      return false;
    }
  }

  // Trae masivamenta las matriculaciones en clases de usuarios Alumnos
  public function getClasesAlumno() {
    try {
      $sql = 'SELECT * FROM user_class';
      $stmt = $this->pdo->prepare($sql);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
      // echo 'Error: ' . $e->getMessage();
      return [];
    }
  }

  // Trae masivamenta las matriculaciones en clases de un usuario Alumno por su ID
  public function getClaseAlumnoByIdAlumno($idUser) {
    try {
      $sql = 'SELECT * FROM user_class WHERE id_user = :id_user';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':id_user', $idUser, PDO::PARAM_STR);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
      // echo 'Error: ' . $e->getMessage();
      return false;
    }
  }

  // Trae masivamenta las matriculaciones en clases de un usuario Alumno por el ID de la clase
  public function getClaseAlumnoByIdClass($idClass) {
    try {
      $sql = 'SELECT * FROM user_class WHERE id_class = :id_class';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':id_class', $idClass, PDO::PARAM_INT);
      $stmt->execute();
      return $stmt->fetchAll(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
      // echo 'Error: ' . $e->getMessage();
      return false;
    }
  }

  // Elimina una matriculacion en clases de un usuario Alumno
  public function deleteClaseAlumnoByIds($id_user, $id_class) {
    try {
      $sql = 'DELETE FROM user_class WHERE id_user = :id_user AND id_class = :id_class';
      $stmt = $this->pdo->prepare($sql);
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
      $stmt->bindParam(':id_class', $id_class, PDO::PARAM_INT);
      return $stmt->execute();
    } catch (PDOException $e) {
      // return 'Error: ' . $e->getMessage();
      return false;
    }
  }
}

?>
