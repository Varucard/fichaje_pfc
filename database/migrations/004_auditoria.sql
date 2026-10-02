-- Migración 004: registro de auditoría (quién hizo qué y cuándo).

CREATE TABLE IF NOT EXISTS auditoria (
  id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  fecha DATETIME NOT NULL,
  id_usuario INT NULL COMMENT 'Administrador que realizó la acción (NULL = sistema o lector)',
  usuario VARCHAR(120) NOT NULL COMMENT 'Nombre del actor al momento de la acción',
  accion VARCHAR(60) NOT NULL COMMENT 'Ej: usuario.alta, pago.eliminacion',
  entidad VARCHAR(40) NULL,
  entidad_id VARCHAR(40) NULL,
  descripcion VARCHAR(500) NOT NULL,
  datos JSON NULL,
  ip VARCHAR(45) NULL,
  KEY idx_auditoria_fecha (fecha),
  KEY idx_auditoria_accion (accion),
  KEY idx_auditoria_entidad (entidad, entidad_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
