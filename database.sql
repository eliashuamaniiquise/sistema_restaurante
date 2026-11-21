-- ============================================================================
-- SISTEMA DR: Restaurante Don Dunay
-- Script de Base de Datos MySQL (9 Tablas Normalizadas + Datos Iniciales)
-- Compatible con XAMPP / MariaDB / MySQL 8.0+
-- ============================================================================

DROP DATABASE IF EXISTS sistemdr;
CREATE DATABASE sistemdr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistemdr;

-- 1. Tabla: usuario (4 roles del sistema)
CREATE TABLE usuario (
  id_usuario INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  clave VARCHAR(255) NOT NULL,
  rol ENUM('administrador','recepcionista','mesero','cocinero') NOT NULL
) ENGINE=InnoDB;

-- 2. Tabla: cliente
CREATE TABLE cliente (
  id_cliente INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  telefono VARCHAR(20) NOT NULL
) ENGINE=InnoDB;

-- 3. Tabla: mesa
CREATE TABLE mesa (
  numero_mesa INT PRIMARY KEY,
  capacidad INT NOT NULL DEFAULT 4,
  estado ENUM('Libre','Ocupada','Reservada') NOT NULL DEFAULT 'Libre'
) ENGINE=InnoDB;

-- 4. Tabla: reserva
CREATE TABLE reserva (
  id_reserva INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NOT NULL,
  numero_mesa INT NOT NULL,
  fecha_hora DATETIME NOT NULL,
  num_comensales INT NOT NULL,
  comentarios VARCHAR(255) DEFAULT '',
  estado ENUM('Pendiente','Confirmada','Cancelada') NOT NULL DEFAULT 'Pendiente',
  FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE CASCADE,
  FOREIGN KEY (numero_mesa) REFERENCES mesa(numero_mesa) ON DELETE CASCADE
) ENGINE=InnoDB;

-- 5. Tabla: plato
CREATE TABLE plato (
  id_plato INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  categoria ENUM('Entradas','Platos de fondo','Bebidas') NOT NULL DEFAULT 'Platos de fondo',
  precio DECIMAL(10,2) NOT NULL,
  disponible TINYINT(1) NOT NULL DEFAULT 1,
  imagen VARCHAR(100) DEFAULT 'plato-default'
) ENGINE=InnoDB;

-- 6. Tabla: insumo
CREATE TABLE insumo (
  id_insumo INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  stock_actual DECIMAL(10,2) NOT NULL,
  stock_minimo DECIMAL(10,2) NOT NULL,
  stock_maximo DECIMAL(10,2) NOT NULL,
  unidad VARCHAR(20) NOT NULL,
  fecha_ingreso DATE DEFAULT (CURRENT_DATE),
  proveedor VARCHAR(100) DEFAULT ''
) ENGINE=InnoDB;

-- 7. Tabla: pedido
CREATE TABLE pedido (
  id_pedido INT AUTO_INCREMENT PRIMARY KEY,
  id_cliente INT NULL,
  numero_mesa INT NOT NULL,
  id_usuario INT NOT NULL,
  fecha_hora DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  notas_generales VARCHAR(255) DEFAULT '',
  estado ENUM('pendiente','listo','pagado') NOT NULL DEFAULT 'pendiente',
  FOREIGN KEY (id_cliente) REFERENCES cliente(id_cliente) ON DELETE SET NULL,
  FOREIGN KEY (numero_mesa) REFERENCES mesa(numero_mesa),
  FOREIGN KEY (id_usuario) REFERENCES usuario(id_usuario)
) ENGINE=InnoDB;

-- 8. Tabla: detalle_pedido
CREATE TABLE detalle_pedido (
  id_detalle INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT NOT NULL,
  id_plato INT NOT NULL,
  cantidad INT NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  notas VARCHAR(255) DEFAULT '',
  FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido) ON DELETE CASCADE,
  FOREIGN KEY (id_plato) REFERENCES plato(id_plato)
) ENGINE=InnoDB;

-- 9. Tabla: factura
CREATE TABLE factura (
  id_factura INT AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT NOT NULL UNIQUE,
  subtotal DECIMAL(10,2) NOT NULL,
  impuestos DECIMAL(10,2) NOT NULL,
  propina DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  total DECIMAL(10,2) NOT NULL,
  metodo_pago ENUM('Efectivo','Tarjeta') NOT NULL,
  fecha_emision DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido)
) ENGINE=InnoDB;

-- ============================================================================
-- DATOS INICIALES DE PRUEBA (SEED DATA)
-- ============================================================================

-- 4 Usuarios (clave de prueba: 1234 para todos)
INSERT INTO usuario (nombre, usuario, clave, rol) VALUES
('Dunay Cervantes', 'admin', '1234', 'administrador'),
('Elias Huamani', 'mesero', '1234', 'mesero'),
('Fharid Cari', 'cocinero', '1234', 'cocinero'),
('Paul Tarifa', 'recepcion', '1234', 'recepcionista');

-- Clientes iniciales
INSERT INTO cliente (nombre, telefono) VALUES
('Elias Mendoza', '958112233'),
('Pedro Castillo', '958445566'),
('Juan Quispe', '958778899'),
('Danan Dunay', '959102030'),
('Camel Menbez', '959405060');

-- 8 Mesas del salón Don Dunay
INSERT INTO mesa (numero_mesa, capacidad, estado) VALUES
(1, 4, 'Libre'),
(2, 3, 'Ocupada'),
(3, 4, 'Ocupada'),
(4, 4, 'Libre'),
(5, 5, 'Reservada'),
(6, 4, 'Libre'),
(7, 4, 'Libre'),
(8, 4, 'Ocupada');

-- Reservas activas
INSERT INTO reserva (id_cliente, numero_mesa, fecha_hora, num_comensales, comentarios, estado) VALUES
(1, 1, CONCAT(CURDATE(), ' 13:30:00'), 4, 'Mesa cerca a ventana', 'Confirmada'),
(2, 2, CONCAT(CURDATE(), ' 14:00:00'), 3, 'Cumpleaños familiar', 'Pendiente'),
(3, 5, CONCAT(CURDATE(), ' 20:00:00'), 5, 'Cena ejecutiva', 'Pendiente');

-- Catálogo de Platos (Entradas, Platos de fondo, Bebidas)
INSERT INTO plato (nombre, categoria, precio, disponible, imagen) VALUES
('Ceviche Mixto', 'Entradas', 40.00, 1, 'ceviche'),
('Papa a la Huancaína', 'Entradas', 18.00, 1, 'huancaina'),
('Causa Rellena de Pollo', 'Entradas', 20.00, 1, 'causa'),
('Arroz con pollo', 'Platos de fondo', 35.00, 1, 'arroz-pollo'),
('Lomo Saltado', 'Platos de fondo', 45.00, 1, 'lomo-saltado'),
('Ají de gallina', 'Platos de fondo', 38.00, 1, 'aji-gallina'),
('Tallarines verdes con bistec', 'Platos de fondo', 32.00, 1, 'tallarines'),
('Milanesa de pollo', 'Platos de fondo', 32.00, 1, 'milanesa'),
('Seco de res con frejoles', 'Platos de fondo', 36.00, 1, 'seco-res'),
('Arroz chaufa de pollo', 'Platos de fondo', 28.00, 1, 'chaufa'),
('Lasaña de carne', 'Platos de fondo', 32.00, 0, 'lasana'),
('Filete de pescado a la plancha', 'Platos de fondo', 34.00, 1, 'pescado'),
('Inca Kola 1 L', 'Bebidas', 12.00, 1, 'inca-kola'),
('Chicha Morada 1 L', 'Bebidas', 15.00, 1, 'chicha'),
('Pisco Sour', 'Bebidas', 25.00, 1, 'pisco-sour');

-- Insumos de inventario
INSERT INTO insumo (nombre, stock_actual, stock_minimo, stock_maximo, unidad, fecha_ingreso, proveedor) VALUES
('Papas (kg)', 200.00, 50.00, 200.00, 'kg', CURDATE(), 'AgroAndes SAC'),
('Pollo deshilachado (kg)', 5.00, 40.00, 100.00, 'kg', CURDATE(), 'Avícola El Sol'),
('Inca Kola 1L', 100.00, 10.00, 100.00, 'unidades', CURDATE(), 'Corporación Lindley'),
('Arroz (kg)', 120.00, 20.00, 200.00, 'kg', CURDATE(), 'Molinos del Sur'),
('Papas fritas (kg)', 8.00, 15.00, 80.00, 'kg', CURDATE(), 'AgroAndes SAC');

-- Pedidos pendientes en Cocina y activos en Mesa 3
INSERT INTO pedido (id_cliente, numero_mesa, id_usuario, fecha_hora, notas_generales, estado) VALUES
(4, 3, 2, NOW(), 'Sin cebolla en entradas', 'pendiente'),
(2, 2, 2, DATE_SUB(NOW(), INTERVAL 6 MINUTE), 'Normal', 'pendiente'),
(3, 8, 2, DATE_SUB(NOW(), INTERVAL 18 MINUTE), 'No picante', 'pendiente');

INSERT INTO detalle_pedido (id_pedido, id_plato, cantidad, subtotal, notas) VALUES
(1, 5, 1, 45.00, 'Término medio'),
(1, 13, 2, 24.00, 'Helada'),
(2, 1, 1, 40.00, 'Sin ají'),
(2, 14, 1, 15.00, ''),
(3, 4, 2, 70.00, 'Extra cremas');

-- Pedidos históricos pagados para Reportes
INSERT INTO pedido (id_cliente, numero_mesa, id_usuario, fecha_hora, estado) VALUES
(4, 1, 2, DATE_SUB(NOW(), INTERVAL 1 DAY), 'pagado'),
(5, 4, 2, DATE_SUB(NOW(), INTERVAL 2 DAY), 'pagado'),
(1, 6, 2, DATE_SUB(NOW(), INTERVAL 3 DAY), 'pagado'),
(3, 7, 2, DATE_SUB(NOW(), INTERVAL 4 DAY), 'pagado');

INSERT INTO factura (id_pedido, subtotal, impuestos, propina, total, metodo_pago, fecha_emision) VALUES
(4, 25.42, 4.58, 0.00, 30.00, 'Efectivo', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5, 33.90, 6.10, 0.00, 40.00, 'Tarjeta', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(6, 50.85, 9.15, 0.00, 60.00, 'Efectivo', DATE_SUB(NOW(), INTERVAL 3 DAY)),
(7, 12.71, 2.29, 0.00, 15.00, 'Tarjeta', DATE_SUB(NOW(), INTERVAL 4 DAY));
