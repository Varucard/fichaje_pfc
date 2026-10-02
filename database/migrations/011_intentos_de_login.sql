-- Migración 011: control de intentos fallidos de inicio de sesión (bloqueo temporal).

CREATE TABLE IF NOT EXISTS intentos_login (
  clave VARCHAR(80) NOT NULL PRIMARY KEY COMMENT 'dni:<DNI> o ip:<IP>',
  fallidos INT NOT NULL DEFAULT 0,
  primer_fallo DATETIME NOT NULL,
  bloqueado_hasta DATETIME NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
