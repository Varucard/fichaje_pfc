<?php

class Database {
  private $host;
  private $database;
  private $username;
  private $password;
  private $pdo;

  public function __construct() {
    $this->host = getenv('MYSQL_DB_HOST') ?? 'localhost';
    $this->database = getenv('MYSQL_DB_NAME') ?? '';
    $this->username = getenv('MYSQL_DB_USER') ?? '';
    $this->password = getenv('MYSQL_DB_PASSWORD') ?? '';

    try {
      $this->pdo = new PDO("mysql:host={$this->host};dbname={$this->database}", $this->username, $this->password);
      $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
      echo "Error de conexión: " . $e->getMessage();
      die();
    }
  }
  
  public function getConnection() {
    return $this->pdo;
  }

}

?>
