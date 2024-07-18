<?php

require_once 'conexion_model.php';

class Pagos {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }

  public function getPagoActualByUser(int $id_user) {
    try {
      $stmt = $this->pdo->prepare("SELECT * FROM `payment` WHERE `id_user` = :id_user ORDER BY `discharge_date` DESC LIMIT 1");
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
      $stmt->execute();
  
      return $stmt->fetch(PDO::FETCH_ASSOC);

  } catch (PDOException $e) {
      echo "Error en la consulta: " . $e->getMessage();
  }
  }

  public function getPagosByUser($id_user) {
    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->prepare("SELECT id_payment, discharge_date, date_of_renovation FROM payments WHERE id_user = :id_user");
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
      $stmt->execute();
      
      // Fetch all results
      $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
      
      if ($result === false) {
          return [];
      }
      
      return $result;
        
    } catch (PDOException $e) {
      echo "Error en la consulta: " . $e->getMessage();
      return [];
    }
  }



  public function cargarPago(array $payment) {

    $id_user = $payment[0];
    $fecha_pago = $payment[1];
    $fecha_renovacion = $payment[2];

    try {
      // Preparar la consulta SQL
      $stmt = $this->pdo->prepare("INSERT INTO `payments`(`id_user`, `discharge_date`, `date_of_renovation`) VALUES (:id_user, :discharge_date, :date_of_renovation)");

      // Enlazar los parámetros
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_STR);
      $stmt->bindParam(':discharge_date', $fecha_pago, PDO::PARAM_STR);
      $stmt->bindParam(':date_of_renovation', $fecha_renovacion, PDO::PARAM_STR);

      // Ejecutar la consulta
      return $stmt->execute();

    } catch (PDOException $e) {
      echo "Error en la consulta: " . $e->getMessage();
      // return false;
    }
}


}

?>
