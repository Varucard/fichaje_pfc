-- Migración 010: pagos de varios meses, promociones y liquidación por asistencia.

CREATE TABLE IF NOT EXISTS promociones (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(80) NOT NULL,
  meses_pagos TINYINT NOT NULL DEFAULT 1 COMMENT 'Meses que se cobran',
  meses_bonificados TINYINT NOT NULL DEFAULT 0 COMMENT 'Meses extra sin cargo',
  descuento DECIMAL(5,2) NOT NULL DEFAULT 0 COMMENT '% de descuento sobre los meses cobrados',
  activa TINYINT(1) NOT NULL DEFAULT 1,
  creada_en DATETIME NOT NULL,
  UNIQUE KEY uq_promociones_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Los pagos anteriores cubren 1 mes.
ALTER TABLE payments
  ADD COLUMN meses_cubiertos TINYINT NOT NULL DEFAULT 1 COMMENT 'Meses que cubre el pago (incluye bonificados)',
  ADD COLUMN id_promocion INT NULL,
  ADD CONSTRAINT fk_payments_promocion FOREIGN KEY (id_promocion) REFERENCES promociones (id) ON DELETE SET NULL;

-- Profesores: modo de liquidación propio (NULL = porcentaje).
ALTER TABLE users
  ADD COLUMN modo_liquidacion ENUM('porcentaje', 'asistencia') NULL,
  ADD COLUMN monto_por_asistencia DECIMAL(10,2) NULL COMMENT 'Modo asistencia: $ por cada ingreso de un alumno a sus clases';

ALTER TABLE liquidaciones
  ADD COLUMN modo ENUM('porcentaje', 'asistencia') NOT NULL DEFAULT 'porcentaje',
  ADD COLUMN monto_por_asistencia DECIMAL(10,2) NULL,
  MODIFY porcentaje DECIMAL(5,2) NULL,
  MODIFY monto_base DECIMAL(12,2) NOT NULL COMMENT 'Modo %: lo cobrado en sus clases. Modo asistencia: cantidad de asistencias';
