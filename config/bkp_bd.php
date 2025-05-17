<?php

session_start();

require_once '../models/conexion_model.php';
require_once '../helpers/url_helper.php';

checkSesion();

$db = new Database();
$pdo = $db->getConnection();

$host = $pdo->getAttribute(PDO::ATTR_CONNECTION_STATUS);
$host = explode(':', $host)[0]; // Solo hostname
$dbname = getenv('MYSQL_DB_NAME');
$user = getenv('MYSQL_DB_USER');
$pass = getenv('MYSQL_DB_PASSWORD');

$backupDir = getenv('BACKUP_DIR') ?: (__DIR__ . '/../backups');

// Crear carpeta si no existe
if (!file_exists($backupDir)) {
  mkdir($backupDir, 0755, true);
}

// Nombre archivo con fecha y hora
$fecha = date('Y-m-d_H-i-s');
$nombreArchivo = "backup_{$fecha}.sql";
$rutaArchivo = rtrim($backupDir, '/') . '/' . $nombreArchivo;

// Función backup simple con PHP (sin mysqldump)
function backupBasico(PDO $pdo, string $rutaArchivo) {
  $tablas = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
  $sql = "";

  foreach ($tablas as $tabla) {
    $res = $pdo->query("SHOW CREATE TABLE `$tabla`")->fetch(PDO::FETCH_ASSOC);
    $sql .= "DROP TABLE IF EXISTS `$tabla`;\n";
    $sql .= $res['Create Table'] . ";\n\n";

    $rows = $pdo->query("SELECT * FROM `$tabla`")->fetchAll(PDO::FETCH_ASSOC);
    foreach ($rows as $row) {
      $valores = array_map(function($value) use ($pdo) {
        if (is_null($value)) {
          return "NULL";
        }
        return $pdo->quote($value);
      }, array_values($row));

      $sql .= "INSERT INTO `$tabla` VALUES (" . implode(", ", $valores) . ");\n";
    }

    $sql .= "\n";
  }

  file_put_contents($rutaArchivo, $sql);
}

// Primero intentamos mysqldump
$comando = "mysqldump --user={$user} --password={$pass} --host={$host} {$dbname} > {$rutaArchivo}";
$resultado = null;
system($comando, $resultado);

if ($resultado === 0) {
  // Backup exitoso con mysqldump
  echo "<script>alert('✅ Respaldo generado correctamente con mysqldump'); window.location.href = '../views/dashboard_view.php';</script>";
} else {
  // Fallback: backup básico con PHP
  try {
    backupBasico($pdo, $rutaArchivo);
    echo "<script>alert('✅ Respaldo generado correctamente con backup PHP'); window.location.href = '../views/dashboard_view.php';</script>";
  } catch (Exception $e) {
    echo "<script>alert('❌ Error al generar el respaldo: " . addslashes($e->getMessage()) . "'); window.location.href = '../views/dashboard_view.php';</script>";
  }
}
