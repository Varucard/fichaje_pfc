-- Reparar una tabla específica (en caso de que falle una tabla en la base de datos 'mysql')
REPAIR TABLE mysql.db;

-- Reparar todas las tablas de todas las bases de datos (excepto 'information_schema' y 'performance_schema')
SELECT CONCAT('REPAIR TABLE ', table_schema, '.', table_name, ';')
FROM information_schema.tables
WHERE table_type = 'BASE TABLE' AND table_schema NOT IN ('information_schema', 'performance_schema');

-- Crear un usuario para la conexión desde Arduino y otorgar privilegios a una IP específica
-- Otorgar privilegios de acceso para el usuario 'root' desde una IP específica
GRANT ALL PRIVILEGES ON *.* TO 'root'@'192.168.1.XX' IDENTIFIED BY 'claveroot';

-- 4. Aplicar los privilegios otorgados
FLUSH PRIVILEGES;
