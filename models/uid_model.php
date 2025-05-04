<?php
require_once 'conexion_model.php';

class UID {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }

  // Método para insertar un nuevo UID desconocido
  public function insertUID($uid) {
    $sql = 'INSERT INTO uid_incomes (uid) VALUES (:uid)';
    $stmt = $this->pdo->prepare($sql);
    $stmt->bindParam(':uid', $uid);
    $stmt->execute();
  }

  // Método para obtener el único UID en la tabla
  public function getLastUID() {
    $sql = 'SELECT uid FROM uid_incomes LIMIT 1'; // No es necesario ordenar ya que solo debe haber un registro
    $stmt = $this->pdo->query($sql);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    
    return $row ? $row['uid'] : null;
  }

  // Método para obtener todos los UIDs desconocidos de la base de datos
  public function getAllUIDs() {
    $sql = 'SELECT uid FROM uid_incomes';
    $stmt = $this->pdo->query($sql);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    return $rows;
  }

  // Método para eliminar el único UID de la tabla
  public function deleteUID() {
    $stmt = $this->pdo->prepare('DELETE FROM uid_incomes');
    $stmt->execute();
  }
}
?>
