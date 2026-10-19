-- VitaSoporte — esquema y datos demo
-- Importar: mysql -u root < bd/vitasoporte.sql

CREATE DATABASE IF NOT EXISTS vitasoporte
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE vitasoporte;

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS pedido_detalle;
DROP TABLE IF EXISTS pedido;
DROP TABLE IF EXISTS producto;
DROP TABLE IF EXISTS categoria;
DROP TABLE IF EXISTS administrador;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE categoria (
  id_categoria INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  imagen VARCHAR(500) NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE producto (
  id_producto INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(150) NOT NULL,
  talla VARCHAR(50) NOT NULL DEFAULT 'Única',
  precio DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  descripcion TEXT NULL,
  disponible TINYINT(1) NOT NULL DEFAULT 1,
  foto_url VARCHAR(500) NULL,
  id_categoria INT UNSIGNED NOT NULL,
  CONSTRAINT fk_producto_categoria
    FOREIGN KEY (id_categoria) REFERENCES categoria(id_categoria)
    ON UPDATE CASCADE ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedido (
  id_pedido INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre_cliente VARCHAR(150) NOT NULL,
  telefono_cliente VARCHAR(30) NOT NULL,
  direccion_entrega VARCHAR(255) NOT NULL,
  fecha DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  total DECIMAL(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE pedido_detalle (
  id_detalle INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  id_pedido INT UNSIGNED NOT NULL,
  id_producto INT UNSIGNED NOT NULL,
  talla VARCHAR(50) NOT NULL DEFAULT 'Única',
  cantidad SMALLINT UNSIGNED NOT NULL DEFAULT 1,
  precio_unitario DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  CONSTRAINT fk_detalle_pedido
    FOREIGN KEY (id_pedido) REFERENCES pedido(id_pedido)
    ON UPDATE CASCADE ON DELETE CASCADE,
  CONSTRAINT fk_detalle_producto
    FOREIGN KEY (id_producto) REFERENCES producto(id_producto)
    ON UPDATE CASCADE ON DELETE RESTRICT,
  UNIQUE KEY uk_pedido_producto_talla (id_pedido, id_producto, talla)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE administrador (
  id_admin INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  nombre VARCHAR(100) NOT NULL,
  correo VARCHAR(150) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin: vitasoporte4@gmail.com / vitasoporte2008
INSERT INTO administrador (nombre, correo, password_hash) VALUES
('Admin VitaSoporte', 'vitasoporte4@gmail.com', '$2y$10$1fNgytJURNMLHKV3TJDfOejDbCfKvOzIYdaaU6./ovIsj.r8wl7f2');

INSERT INTO categoria (nombre, imagen) VALUES
('Postura', 'img/cat-postura.jpg'),
('Circulación', 'img/cat-circulacion.jpg'),
('Deportivas', 'img/cat-deportivas.jpg'),
('Postparto', 'img/cat-postparto.jpg');

-- talla = tallas disponibles (el cliente elige una al agregar a la bolsa)
INSERT INTO producto (nombre, talla, precio, descripcion, disponible, foto_url, id_categoria) VALUES
('Faja postural lumbar Vita', 'S,M,L,XL', 89000.00, 'Soporte lumbar firme para jornada laboral. Tela transpirable y cierre de tres niveles.', 1, 'img/prod-faja-lumbar.jpg', 1),
('Chaleco corrector discreto', 'S,M,L,XL', 115000.00, 'Diseño invisible bajo la ropa. Ayuda a alinear hombros y espalda alta.', 1, 'img/prod-chaleco.webp', 1),
('Faja abdominal media presión', 'S,M,L,XL', 78000.00, 'Compresión suave para silueta y soporte abdominal diario sin marcar.', 1, 'img/prod-faja-abdominal.webp', 1),
('Corrector de postura ligero', 'S,M,L,XL', 98000.00, 'Soporte de espalda con tirantes ergonómicos. Ideal para home office.', 1, 'img/prod-corrector.webp', 1),
('Media de compresión graduada', 'S,M,L,XL', 65000.00, 'Compresión 15-20 mmHg. Ideal para viajes largos y trabajo de pie.', 1, 'img/prod-medias.jpg', 2),
('Panty de compresión ligera', 'S,M,L,XL', 72000.00, 'Mejora el retorno venoso con comodidad de uso continuo.', 1, 'img/prod-panty.webp', 2),
('Mangas de compresión brazos', 'Única', 54000.00, 'Soporte para brazos en recuperación y deporte de resistencia.', 0, 'img/prod-mangas.jpg', 2),
('Faja deportiva cintura', 'S,M,L,XL', 95000.00, 'Estabilidad de core en entrenamiento. Secado rápido y costuras planas.', 1, 'img/prod-faja-sport.webp', 3),
('Top de compresión training', 'S,M,L,XL', 82000.00, 'Soporte medio-alto para running y HIIT. Tejido elástico de alto retorno.', 1, 'img/prod-top.webp', 3),
('Shorts de compresión unisex', 'S,M,L,XL', 88000.00, 'Reduce fatiga muscular. Ideal para ciclismo, gym y trote.', 1, 'img/prod-shorts.webp', 3),
('Cinturilla deportiva ajustable', 'Única', 69000.00, 'Cierre velcro de doble ajuste. Perfecta para sesiones de fuerza.', 1, 'img/prod-cinturilla.jpg', 3),
('Faja postparto alta', 'S,M,L,XL', 125000.00, 'Recuperación abdominal postparto. Algodón suave y tres niveles de cierre.', 1, 'img/prod-postparto.jpg', 4);

INSERT INTO pedido (nombre_cliente, telefono_cliente, direccion_entrega, fecha, total) VALUES
('Laura Méndez', '3001234567', 'Calle 45 #12-30, Bogotá', '2026-03-10 09:15:00', 89000.00),
('Carlos Ruiz', '3109876543', 'Carrera 7 #80-12, Medellín', '2026-03-12 14:40:00', 130000.00),
('Ana Torres', '3205557788', 'Av. El Dorado #68-40, Bogotá', '2026-03-14 11:05:00', 95000.00),
('Sofía Vargas', '3012223344', 'Calle 100 #19-45, Bogotá', '2026-03-15 16:20:00', 125000.00),
('Diego Palacios', '3156677889', 'Calle 10 #5-20, Cali', '2026-03-16 10:05:00', 115000.00),
('Valentina Ríos', '3184455667', 'Carrera 50 #70-15, Barranquilla', '2026-03-17 18:30:00', 82000.00),
('Mariana Gómez', '3009988776', 'Calle 26 #45-10, Bogotá', '2026-03-18 12:10:00', 72000.00),
('Andrés Molina', '3123344556', 'Av. Santander #32-08, Manizales', '2026-03-19 08:45:00', 88000.00);

INSERT INTO pedido_detalle (id_pedido, id_producto, talla, cantidad, precio_unitario) VALUES
(1, 1, 'M', 1, 89000.00),
(2, 5, 'L', 2, 65000.00),
(3, 8, 'M', 1, 95000.00),
(4, 12, 'M', 1, 125000.00),
(5, 2, 'L', 1, 115000.00),
(6, 9, 'S', 1, 82000.00),
(7, 6, 'L', 1, 72000.00),
(8, 10, 'L', 1, 88000.00);
