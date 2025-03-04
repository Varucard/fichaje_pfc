<?php

require_once 'conexion_model.php';

class Pagos {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }

  // Trae el ultimo pago registrado por un usuario Alumno
  public function getPagoActualByUser(int $id_user) {
    try {
      $stmt = $this->pdo->prepare("SELECT * FROM `payments` WHERE `id_user` = :id_user ORDER BY `discharge_date` DESC LIMIT 1");
      $stmt->bindParam(':id_user', $id_user, PDO::PARAM_INT);
      $stmt->execute();
  
      return $stmt->fetch(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Trae los pagos de un usuario Alumno
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
      // echo "Error en la consulta: " . $e->getMessage();
      return [];
    }
  }

  // Registra el pago de un usuario Alumno
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
      if ($stmt->execute())
        return true;

    } catch (PDOException $e) {
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Trae la ultima fecha de pago de los pagos de un usuario Alumno
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
      // echo "Error en la consulta: " . $e->getMessage();
      return false;
    }
  }

  // Eliminar un pago por su ID
  public function deletePagoById($id_payment) {
    try {
      $stmt = $this->pdo->prepare("DELETE FROM payments WHERE id_payment = :id_payment");
      $stmt->bindParam(':id_payment', $id_payment, PDO::PARAM_INT);

      return $stmt->execute(); // Retorna true si la eliminación fue exitosa, false si falló
    } catch (PDOException $e) {
      // echo 'Error: ' . $e->getMessage(); // Para depuración, si lo necesitas
      return false;
    }
  }

}

?>
