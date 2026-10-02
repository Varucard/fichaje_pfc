-- Migración 009: horarios de las clases y clase asignada a cada fichada.

CREATE TABLE IF NOT EXISTS clase_horarios (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_class INT NOT NULL,
  dia_semana TINYINT NOT NULL COMMENT '1 = lunes ... 7 = domingo (ISO-8601)',
  hora_inicio TIME NOT NULL,
  hora_fin TIME NOT NULL,
  KEY idx_horarios_dia (dia_semana),
  CONSTRAINT fk_horarios_clase FOREIGN KEY (id_class) REFERENCES classes (id_class) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE incomes
  ADD COLUMN id_class INT NULL COMMENT 'Clase deducida por horario (NULL = fuera de horario)',
  ADD CONSTRAINT fk_incomes_clase FOREIGN KEY (id_class) REFERENCES classes (id_class) ON DELETE SET NULL;
