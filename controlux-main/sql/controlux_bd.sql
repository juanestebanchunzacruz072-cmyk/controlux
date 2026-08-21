-- =========================================
-- ESTRUCTURA DE BASE DE DATOS: controlux_bd
-- Proyecto: Controlux / JC URBAN
-- =========================================

-- 1. Tabla Rol
CREATE TABLE `Rol` (
  `id_rol` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(50) UNIQUE NOT NULL,
  `descripcion` varchar(150)
);

-- 2. Tabla Usuario (Unificada con roles)
CREATE TABLE `Usuario` (
  `id_usuario` int PRIMARY KEY AUTO_INCREMENT,
  `id_rol` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) NOT NULL,
  `correo` varchar(150) UNIQUE NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20),
  `direccion` varchar(200),
  `ciudad` varchar(100),
  `activo` boolean NOT NULL DEFAULT true,
  `fecha_registro` datetime NOT NULL,
  FOREIGN KEY (`id_rol`) REFERENCES `Rol` (`id_rol`)
);

-- 3. Tabla Marca
CREATE TABLE `Marca` (
  `id_marca` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(100) UNIQUE NOT NULL,
  `descripcion` varchar(255),
  `activo` boolean NOT NULL DEFAULT true
);

-- 4. Tabla Categoria
CREATE TABLE `Categoria` (
  `id_categoria` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(100) UNIQUE NOT NULL,
  `descripcion` varchar(255),
  `activo` boolean NOT NULL DEFAULT true
);

-- 5. Tabla Subcategoria
CREATE TABLE `Subcategoria` (
  `id_subcategoria` int PRIMARY KEY AUTO_INCREMENT,
  `id_categoria` int NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(255),
  `activo` boolean NOT NULL DEFAULT true,
  FOREIGN KEY (`id_categoria`) REFERENCES `Categoria` (`id_categoria`)
);

-- 6. Tabla Producto
CREATE TABLE `Producto` (
  `id_producto` int PRIMARY KEY AUTO_INCREMENT,
  `id_marca` int NOT NULL,
  `id_categoria` int NOT NULL,
  `id_subcategoria` int, 
  `referencia` varchar(50) UNIQUE NOT NULL,
  `nombre` varchar(150) NOT NULL,
  `descripcion` text NOT NULL,
  `precio` decimal(12,2) NOT NULL,
  `stock` int NOT NULL DEFAULT 0,
  `activo` boolean NOT NULL DEFAULT true,
  `fecha_creacion` datetime NOT NULL,
  `fecha_actualizacion` datetime,
  FOREIGN KEY (`id_marca`) REFERENCES `Marca` (`id_marca`),
  FOREIGN KEY (`id_categoria`) REFERENCES `Categoria` (`id_categoria`),
  FOREIGN KEY (`id_subcategoria`) REFERENCES `Subcategoria` (`id_subcategoria`)
);

-- 7. Tabla Imagen_Producto
CREATE TABLE `Imagen_Producto` (
  `id_imagen` int PRIMARY KEY AUTO_INCREMENT,
  `id_producto` int NOT NULL,
  `url_imagen` varchar(500) NOT NULL,
  `nombre_imagen` varchar(150),
  `principal` boolean NOT NULL DEFAULT false,
  `fecha_creacion` datetime NOT NULL,
  FOREIGN KEY (`id_producto`) REFERENCES `Producto` (`id_producto`)
);

-- 8. Tabla Carrito
CREATE TABLE `Carrito` (
  `id_carrito` int PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `estado` varchar(30) NOT NULL DEFAULT 'ACTIVO',
  `fecha_creacion` datetime NOT NULL,
  `fecha_actualizacion` datetime,
  FOREIGN KEY (`id_usuario`) REFERENCES `Usuario` (`id_usuario`)
);

-- 9. Tabla Detalle_Carrito
CREATE TABLE `Detalle_Carrito` (
  `id_detalle_carrito` int PRIMARY KEY AUTO_INCREMENT,
  `id_carrito` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  FOREIGN KEY (`id_carrito`) REFERENCES `Carrito` (`id_carrito`),
  FOREIGN KEY (`id_producto`) REFERENCES `Producto` (`id_producto`)
);

-- 10. Tabla Estado_Pedido
CREATE TABLE `Estado_Pedido` (
  `id_estado` int PRIMARY KEY AUTO_INCREMENT,
  `nombre` varchar(50) UNIQUE NOT NULL,
  `descripcion` varchar(150)
);

-- 11. Tabla Pedidos
CREATE TABLE `Pedidos` (
  `id_pedido` int PRIMARY KEY AUTO_INCREMENT,
  `id_usuario` int NOT NULL,
  `id_estado` int NOT NULL,
  `fecha_pedido` datetime NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  `total` decimal(12,2) NOT NULL,
  `direccion_entrega` varchar(250),
  `observaciones` text,
  FOREIGN KEY (`id_usuario`) REFERENCES `Usuario` (`id_usuario`),
  FOREIGN KEY (`id_estado`) REFERENCES `Estado_Pedido` (`id_estado`)
);

-- 12. Tabla Detalle_Pedidos
CREATE TABLE `Detalle_Pedidos` (
  `id_detalle_pedido` int PRIMARY KEY AUTO_INCREMENT,
  `id_pedido` int NOT NULL,
  `id_producto` int NOT NULL,
  `cantidad` int NOT NULL,
  `precio_unitario` decimal(12,2) NOT NULL,
  `subtotal` decimal(12,2) NOT NULL,
  FOREIGN KEY (`id_pedido`) REFERENCES `Pedidos` (`id_pedido`),
  FOREIGN KEY (`id_producto`) REFERENCES `Producto` (`id_producto`)
);

-- =========================================
-- DATOS INICIALES (SEMILLAS / SEEDERS)
-- =========================================

-- Insertar Roles
INSERT INTO `Rol` (`nombre`, `descripcion`) VALUES 
('Administrador', 'Acceso total al panel de control'),
('Cliente', 'Usuario comprador de la tienda web');

-- Insertar Estados de Pedido Básicos
INSERT INTO `Estado_Pedido` (`nombre`, `descripcion`) VALUES 
('Pendiente', 'Pedido creado, a la espera de confirmación'),
('Pagado', 'Pago verificado exitosamente'),
('Enviado', 'El pedido ya fue entregado a la transportadora'),
('Entregado', 'El cliente recibió su producto');

-- Insertar Categorías
INSERT INTO `Categoria` (`nombre`, `descripcion`) VALUES 
('Accesorios', 'Categoría principal de accesorios'),
('Perfumes', 'Categoría principal de fragancias y perfumes'),
('Relojes', 'Categoría principal de relojería');

-- Insertar Subcategorías
INSERT INTO `Subcategoria` (`id_categoria`, `nombre`, `descripcion`) VALUES 
(1, 'Billeteras', 'Billeteras de lujo'),
(1, 'Carrieles', 'Carrieles exclusivos'),
(1, 'Correas', 'Correas de diseño'),
(1, 'Gorras', 'Gorras urbanas'),
(2, '1.1', 'Perfumes calidad 1.1'),
(3, 'Original', 'Relojes 100% originales'),
(3, 'Richard', 'Relojes marca Richard Mille'),
(3, 'Rolex', 'Relojes marca Rolex'),
(3, 'Patek', 'Marca de relojes de lujo y prestigio mundial'),
(3, 'AP', 'Marca de relojes de alta gama y diseño técnico');

-- Insertar Marcas
INSERT IGNORE INTO `Marca` (`nombre`, `descripcion`) VALUES 
('Lattafa', 'Marca especializada en perfumes árabes de alta calidad'),
('ARMAF', 'Marca de perfumes y fragancias exclusivas'),
('Rolex', 'Marca de relojes de lujo y prestigio mundial'),
('Richard Mille', 'Marca de relojes de alta gama y diseño técnico'),
('Louis Vuitton', 'Marca francesa de moda, accesorios y artículos de lujo'),
('Coach', 'Marca de accesorios, bolsos y moda de diseño'),
('Amiri', 'Marca de moda urbana de lujo y accesorios'),
('Gucci', 'Gucci es una famosa casa de moda de lujo italiana'),
('Hugo Boss', 'Marca de moda, accesorios y perfumes de diseñador'),
('Jordan', 'Marca deportiva y urbana de ropa, gorras y calzado'),
('New Era', 'Marca líder en gorras urbanas y accesorios deportivos');