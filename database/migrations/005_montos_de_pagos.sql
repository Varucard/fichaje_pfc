-- Migración 005: montos en los pagos y reparto por clase.
-- Los pagos anteriores quedan con monto NULL: se consideran pagos completos (sin saldo).

ALTER TABLE payments
  ADD COLUMN monto DECIMAL(10,2) NULL COMMENT 'Monto cobrado. NULL = pago histórico sin monto (se considera completo)',
  ADD COLUMN monto_cuota DECIMAL(10,2) NULL COMMENT 'Cuota que correspondía al pagar (suma de las clases del alumno)';

-- Parte de cada pago asignada a cada clase, proporcional a su precio.
-- Se guarda una copia del nombre y precio por si la clase cambia o se elimina.
CREATE TABLE IF NOT EXISTS payment_classes (
  id_payment_class INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_payment INT NOT NULL,
  id_class INT NULL,
  nombre_clase VARCHAR(150) NOT NULL,
  precio_clase DECIMAL(10,2) NOT NULL,
  monto DECIMAL(10,2) NOT NULL,
  KEY idx_payment_classes_clase (id_class),
  CONSTRAINT fk_payment_classes_pago FOREIGN KEY (id_payment) REFERENCES payments (id_payment) ON DELETE CASCADE,
  CONSTRAINT fk_payment_classes_clase FOREIGN KEY (id_class) REFERENCES classes (id_class) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
