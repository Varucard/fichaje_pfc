<?php

// TODO: Revisar, no se que hace este modelo aca
require_once 'conexion_model.php';

class ProfesorClase {
  private $database;
  private $pdo;

  public function __construct() {
    $this->database = new Database();
    $this->pdo = $this->database->getConnection();
  }  

}

?>
