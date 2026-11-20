CREATE DATABASE IF NOT EXISTS sistemdr CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sistemdr;

CREATE TABLE usuario (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  usuario VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  rol ENUM('Administrador','Recepcionista','Mesero','Cocinero') NOT NULL,
  estado TINYINT(1) NOT NULL DEFAULT 1,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE cliente (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  telefono VARCHAR(20),
  email VARCHAR(100),
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE mesa (
  id INT AUTO_INCREMENT PRIMARY KEY,
  numero VARCHAR(20) NOT NULL UNIQUE,
  capacidad INT NOT NULL DEFAULT 4,
  estado ENUM('Disponible','Ocupada','Reservada','Mantenimiento') NOT NULL DEFAULT 'Disponible'
) ENGINE=InnoDB;

CREATE TABLE reserva (
  id INT AUTO_INCREMENT PRIMARY KEY,
  cliente_id INT NOT NULL,
  mesa_id INT NOT NULL,
  fecha_hora DATETIME NOT NULL,
  personas INT NOT NULL,
  estado ENUM('Pendiente','Confirmada','Cancelada','Finalizada') DEFAULT 'Pendiente',
  observacion VARCHAR(255),
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (cliente_id) REFERENCES cliente(id),
  FOREIGN KEY (mesa_id) REFERENCES mesa(id)
) ENGINE=InnoDB;

CREATE TABLE plato (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  categoria VARCHAR(50) NOT NULL,
  descripcion VARCHAR(255),
  precio DECIMAL(10,2) NOT NULL,
  stock INT NOT NULL DEFAULT 0,
  activo TINYINT(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB;

CREATE TABLE insumo (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  unidad VARCHAR(20) NOT NULL,
  stock DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock_minimo DECIMAL(10,2) NOT NULL DEFAULT 0
) ENGINE=InnoDB;

CREATE TABLE pedido (
  id INT AUTO_INCREMENT PRIMARY KEY,
  mesa_id INT,
  usuario_id INT NOT NULL,
  tipo ENUM('Mesa','Llevar','Delivery') NOT NULL DEFAULT 'Mesa',
  estado ENUM('Pendiente','En preparacion','Listo','Servido','Cancelado') NOT NULL DEFAULT 'Pendiente',
  total DECIMAL(10,2) NOT NULL DEFAULT 0,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (mesa_id) REFERENCES mesa(id),
  FOREIGN KEY (usuario_id) REFERENCES usuario(id)
) ENGINE=InnoDB;

CREATE TABLE detalle_pedido (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL,
  plato_id INT NOT NULL,
  cantidad INT NOT NULL,
  precio_unitario DECIMAL(10,2) NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (pedido_id) REFERENCES pedido(id) ON DELETE CASCADE,
  FOREIGN KEY (plato_id) REFERENCES plato(id)
) ENGINE=InnoDB;

CREATE TABLE factura (
  id INT AUTO_INCREMENT PRIMARY KEY,
  pedido_id INT NOT NULL UNIQUE,
  cliente_nombre VARCHAR(100) NOT NULL,
  documento VARCHAR(20),
  tipo_pago ENUM('Efectivo','Tarjeta') NOT NULL,
  subtotal DECIMAL(10,2) NOT NULL,
  igv DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (pedido_id) REFERENCES pedido(id)
) ENGINE=InnoDB;

INSERT INTO usuario (nombre,usuario,password,rol) VALUES
('Administrador','admin','$2y$10$9TH314iAtdVS8KPum44EDurGxyhsIoLYvNIfoJsUriYle.L8vuzTS','Administrador'),
('Carlos Recepción','recepcion','$2y$10$9TH314iAtdVS8KPum44EDurGxyhsIoLYvNIfoJsUriYle.L8vuzTS','Recepcionista'),
('Ana Mesera','mesero','$2y$10$9TH314iAtdVS8KPum44EDurGxyhsIoLYvNIfoJsUriYle.L8vuzTS','Mesero'),
('Luis Cocina','cocina','$2y$10$9TH314iAtdVS8KPum44EDurGxyhsIoLYvNIfoJsUriYle.L8vuzTS','Cocinero');

INSERT INTO mesa (numero,capacidad,estado) VALUES
('1',4,'Disponible'),('2',4,'Disponible'),('3',6,'Disponible'),('4',2,'Disponible'),('5',8,'Disponible');

INSERT INTO cliente (nombre,telefono,email) VALUES
('Cliente general','', ''),('Maria López','999111222','maria@example.com');

INSERT INTO plato (nombre,categoria,descripcion,precio,stock) VALUES
('Lomo saltado','Platos de fondo','Lomo de res con papas y arroz',28.50,20),
('Ceviche clásico','Entradas','Pescado fresco al limón',22.00,18),
('Ají de gallina','Platos de fondo','Pollo deshilachado en crema de ají',20.00,25),
('Papas a la huancaína','Entradas','Papas con salsa de queso',16.00,30),
('Chicha morada','Bebidas','Refresco tradicional',8.00,40),
('Coca Cola','Bebidas','Gaseosa helada',6.00,50);

INSERT INTO insumo (nombre,unidad,stock,stock_minimo) VALUES
('Arroz','kg',12.00,5.00),('Papa','kg',20.00,8.00),('Pescado','kg',8.00,3.00),('Pollo','kg',15.00,6.00),('Aceite','litros',6.00,2.00);
