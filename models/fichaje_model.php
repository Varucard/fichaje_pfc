<?php

require_once 'conexion_model.php';

class Fichajes {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }

  public function getUltimosFichajes($limite = 10) {
    try {
      // Consulta para obtener los últimos fichajes
      $stmt = $this->pdo->prepare("
        SELECT i.*, u.rfid, u.dni, CONCAT(u.name, ' ', u.surname) AS alumno
        FROM incomes i
        JOIN users u ON i.id_user = u.id_user
        ORDER BY i.addmission_date DESC
        LIMIT :limite
      ");
      $stmt->bindValue(':limite', $limite, PDO::PARAM_INT);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  public function guardarFichada($id_user, $addmission_date) {
    try {      
      // Consulta SQL para insertar la fichada en la tabla incomes
      $sql = "INSERT INTO incomes (id_user, addmission_date) VALUES (:id_user, :addmission_date)";
      $stmt = $this->pdo->prepare($sql);
      
      // Bind de los parámetros
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_STR);
      $stmt->bindParam(':addmission_date', $addmission_date, PDO::PARAM_STR);
      
      // Ejecutar la consulta
      $stmt->execute();
      
      // Retornar verdadero si la inserción fue exitosa
      return true;
    } catch (PDOException $e) {
      // $error = $e->getMessage();
      return false;

    }
  }

  public function getUltimosFichajesByUser($busqueda) {
    try {
      // Consulta para buscar fichajes basados en el término de búsqueda
      $stmt = $this->pdo->prepare("
        SELECT i.*, u.rfid, u.dni, CONCAT(u.name, ' ', u.surname) AS alumno
        FROM incomes i
        JOIN users u ON i.id_user = u.id_user
        WHERE u.name LIKE :busqueda OR u.surname LIKE :busqueda OR u.dni LIKE :busqueda OR u.rfid LIKE :busqueda
        ORDER BY i.addmission_date DESC
      ");
      $stmt->bindValue(':busqueda', '%' . $busqueda . '%', PDO::PARAM_STR);
      $stmt->execute();

      return $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }
}

?>
