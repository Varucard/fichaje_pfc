-- Migración 007: stock de productos (guantes, vendas, bebidas, etc.).

CREATE TABLE IF NOT EXISTS productos (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(120) NOT NULL,
  descripcion VARCHAR(255) NULL,
  precio DECIMAL(10,2) NOT NULL DEFAULT 0 COMMENT 'Precio de venta',
  stock INT NOT NULL DEFAULT 0,
  stock_minimo INT NOT NULL DEFAULT 0 COMMENT 'Debajo de este valor se avisa en el panel',
  activo TINYINT(1) NOT NULL DEFAULT 1,
  creado_en DATETIME NOT NULL,
  UNIQUE KEY uq_productos_nombre (nombre)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

CREATE TABLE IF NOT EXISTS movimientos_stock (
  id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  id_producto INT NOT NULL,
  tipo ENUM('entrada', 'venta', 'ajuste') NOT NULL,
  cantidad INT NOT NULL COMMENT 'Positiva = ingresa, negativa = sale',
  stock_resultante INT NOT NULL,
  precio_unitario DECIMAL(10,2) NULL COMMENT 'Venta: precio cobrado. Entrada: costo (opcional)',
  total DECIMAL(12,2) NULL,
  id_cliente INT NULL COMMENT 'Venta a un cliente registrado (opcional)',
  observacion VARCHAR(255) NULL,
  id_admin INT NULL,
  fecha DATETIME NOT NULL,
  KEY idx_movimientos_producto_fecha (id_producto, fecha),
  KEY idx_movimientos_tipo_fecha (tipo, fecha),
  CONSTRAINT fk_movimientos_producto FOREIGN KEY (id_producto) REFERENCES productos (id),
  CONSTRAINT fk_movimientos_cliente FOREIGN KEY (id_cliente) REFERENCES users (id_user)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
