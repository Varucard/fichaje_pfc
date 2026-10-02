-- Migración 008: emails (cola de envío, configuración de avisos y baja de comunicaciones).

ALTER TABLE users
  ADD COLUMN acepta_emails TINYINT(1) NOT NULL DEFAULT 1 COMMENT '0 = se dio de baja de los avisos (los comprobantes se envían igual)',
  ADD COLUMN token_baja CHAR(32) NULL COMMENT 'Token del link para darse de baja de los avisos',
  ADD UNIQUE KEY uq_users_token_baja (token_baja);

CREATE TABLE IF NOT EXISTS emails_cola (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  tipo VARCHAR(40) NOT NULL COMMENT 'Ej: vencimiento, deuda, comprobante, bienvenida',
  id_usuario INT NULL,
  destinatario VARCHAR(190) NOT NULL,
  nombre_destinatario VARCHAR(160) NULL,
  asunto VARCHAR(200) NOT NULL,
  cuerpo_html MEDIUMTEXT NOT NULL,
  cuerpo_texto TEXT NOT NULL,
  adjunto VARCHAR(120) NULL COMMENT 'Adjunto a generar al enviar. Ej: comprobante:123',
  clave_unica VARCHAR(150) NULL COMMENT 'Evita enviar dos veces el mismo aviso',
  estado ENUM('pendiente', 'enviado', 'error', 'cancelado') NOT NULL DEFAULT 'pendiente',
  intentos TINYINT NOT NULL DEFAULT 0,
  ultimo_error VARCHAR(500) NULL,
  creado_en DATETIME NOT NULL,
  enviar_desde DATETIME NOT NULL,
  enviado_en DATETIME NULL,
  UNIQUE KEY uq_emails_clave (clave_unica),
  KEY idx_emails_estado (estado, enviar_desde),
  KEY idx_emails_usuario (id_usuario),
  CONSTRAINT fk_emails_usuario FOREIGN KEY (id_usuario) REFERENCES users (id_user) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Configuración editable desde el panel (clave/valor).
CREATE TABLE IF NOT EXISTS configuracion (
  clave VARCHAR(80) NOT NULL PRIMARY KEY,
  valor VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
