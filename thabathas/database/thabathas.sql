CREATE DATABASE IF NOT EXISTS thabathas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE thabathas;

CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    rol ENUM('cliente','admin') NOT NULL DEFAULT 'cliente',
    activo TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(120) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    imagen VARCHAR(255),
    disponible TINYINT(1) NOT NULL DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE pedidos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED NULL,
    nombre_cliente VARCHAR(120) NOT NULL,
    telefono VARCHAR(30),
    modalidad ENUM('retiro','delivery') NOT NULL,
    direccion TEXT,
    total DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado ENUM('pendiente','contactado','completado','cancelado') NOT NULL DEFAULT 'pendiente',
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pedidos_usuario FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

CREATE TABLE detalle_pedido (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    pedido_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NULL,
    nombre_producto VARCHAR(120) NOT NULL,
    precio DECIMAL(10,2) NOT NULL,
    cantidad INT UNSIGNED NOT NULL,
    subtotal DECIMAL(10,2) NOT NULL,
    CONSTRAINT fk_detalle_pedido FOREIGN KEY (pedido_id) REFERENCES pedidos(id) ON DELETE CASCADE,
    CONSTRAINT fk_detalle_producto FOREIGN KEY (producto_id) REFERENCES productos(id) ON DELETE SET NULL
);

INSERT INTO usuarios (nombre, email, password, rol) VALUES
('Administrador THABATHA’S', 'admin@thabathas.com', '$2y$12$gjZFX7PQapOdwXKW9McvouWbtEGrIaFghX6Qh0yyn81BN8Wrsi526', 'admin'),
('Cliente de Prueba', 'cliente@thabathas.com', '$2y$12$Y8xRCjCKfCy8lF6mx0l9SOiRRnEWJEbNIrsd88r4btJDROLINJo3K', 'cliente');

INSERT INTO productos (nombre, descripcion, precio, imagen, disponible) VALUES
('Bistec', 'Corte de carne para preparar a la plancha o sartén.', 0.00, NULL, 1),
('Carne para guisar', 'Corte seleccionado para guisos y preparaciones caseras.', 0.00, NULL, 1),
('Carne para mechar', 'Corte de carne ideal para preparar carne mechada.', 0.00, NULL, 1),
('Carne molida', 'Carne molida para diferentes preparaciones.', 0.00, NULL, 1),
('Chorizo', 'Chorizo para parrillas y diferentes preparaciones.', 0.00, NULL, 1),
('Costilla', 'Costilla de res para diferentes preparaciones.', 0.00, NULL, 1),
('Lagarto con hueso', 'Corte de res con hueso.', 0.00, NULL, 1),
('Lagarto sin hueso', 'Corte de res sin hueso.', 0.00, NULL, 1),
('Hueso rojo', 'Hueso de res para sopas y caldos.', 0.00, NULL, 1),
('Solomo', 'Corte de res para diferentes preparaciones.', 0.00, NULL, 1),
('Punta trasera', 'Corte de res para parrillas y preparaciones especiales.', 0.00, NULL, 1),
('Muchacho redondo', 'Corte de res para asados y preparaciones al horno.', 0.00, NULL, 1),
('Muchacho cuadrado', 'Corte de res para diferentes preparaciones.', 0.00, NULL, 1);
