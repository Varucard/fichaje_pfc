<?php

// Función para cargar las variables desde .env
function cargarEnv($archivo) {
    if (!file_exists($archivo)) {
        return;
    }

    $lineas = file($archivo, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lineas as $linea) {
        if (strpos(trim($linea), '#') === 0) continue; // Saltar comentarios
        list($nombre, $valor) = explode('=', $linea, 2);
        putenv(trim($nombre) . '=' . trim($valor));
    }
}

// Cargar .env manualmente
cargarEnv(__DIR__ . '/.env');

// Definir constantes usando getenv()
define('APPROOT', dirname(dirname(__FILE__)));
define('URLROOT', getenv('URLROOT') ?: 'http://localhost/fichaje_pfc');
define('SITENAME', getenv('SITENAME') ?: 'Palillo Fight Club');
define('APPVERSION', getenv('APPVERSION') ?: '1.0.0');

define('MYSQL_DB_HOST', getenv('MYSQL_DB_HOST'));
define('MYSQL_DB_USER', getenv('MYSQL_DB_USER'));
define('MYSQL_DB_PASSWORD', getenv('MYSQL_DB_PASSWORD'));
define('MYSQL_DB_NAME', getenv('MYSQL_DB_NAME'));
define('MYSQL_DB_PORT', getenv('MYSQL_DB_PORT'));
define('IP_ARDUINO', getenv('IP_ARDUINO'));

// Verificar si las variables se cargaron correctamente (para pruebas)
// echo getenv('MYSQL_DB_HOST');

?>
