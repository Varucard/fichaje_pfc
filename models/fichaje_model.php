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

  public function getUltimaFechaPago($id_user) {
    try {
      $stmt = $this->pdo->prepare("
        SELECT date_of_renovation 
        FROM payments 
        WHERE id_user = :id_user 
        ORDER BY date_of_renovation DESC 
        LIMIT 1
      ");
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
      $stmt->execute();

      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      return $result ? $result['date_of_renovation'] : null;

    } catch (PDOException $e) {
      echo "Error en la consulta: " . $e->getMessage();
      return null;
    }
  }
}

?>
