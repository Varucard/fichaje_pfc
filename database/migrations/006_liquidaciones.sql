-- Migración 006: liquidación de profesores.

ALTER TABLE users
  ADD COLUMN porcentaje_liquidacion DECIMAL(5,2) NULL
    COMMENT 'Profesores: % de lo cobrado en sus clases que se le liquida (NULL = valor por defecto)';

CREATE TABLE IF NOT EXISTS liquidaciones (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_profesor INT NOT NULL,
  periodo CHAR(7) NOT NULL COMMENT 'AAAA-MM',
  monto_base DECIMAL(12,2) NOT NULL COMMENT 'Lo cobrado en sus clases en el período',
  porcentaje DECIMAL(5,2) NOT NULL,
  monto DECIMAL(12,2) NOT NULL COMMENT 'Monto a pagar al profesor',
  detalle JSON NULL COMMENT 'Cálculo por clase',
  fecha_registro DATETIME NOT NULL,
  id_admin INT NULL,
  pagada TINYINT(1) NOT NULL DEFAULT 0,
  fecha_pago DATETIME NULL,
  UNIQUE KEY uq_liquidacion_profesor_periodo (id_profesor, periodo),
  KEY idx_liquidaciones_periodo (periodo),
  CONSTRAINT fk_liquidaciones_profesor FOREIGN KEY (id_profesor) REFERENCES users (id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
