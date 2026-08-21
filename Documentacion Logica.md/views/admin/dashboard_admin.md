# Documentación: `views/admin/dashboard_admin.php`

Esta es la pantalla principal del panel de administración. Aquí se resumen todas las métricas de la tienda (total de productos, clientes, pedidos).

### Explicación

#### 1. Protección de la Vista
```php
<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../auth/login.php");
    exit;
}
require_once '../../config/database.php';
```
- **Si te borran la línea 4 o 5 (`if (!isset...`)**: Cualquier persona que sepa la URL (`/views/admin/dashboard_admin.php`) podría entrar al panel de administración sin iniciar sesión, o peor aún, un cliente (rol 2) podría entrar al panel. Esta línea es crucial para la seguridad.
- **Si te borran `require_once`**: La página dará un `Fatal Error: Undefined variable $conn`, porque todas las consultas SQL de abajo dependen de la conexión a la BD.

#### 2. Consultas de Métricas (Tarjetas)
El dashboard hace varios `SELECT COUNT(*)` para calcular cuántos registros hay en las tablas.
```php
// 1. Total Productos
$stmt = $conn->query("SELECT COUNT(*) as total FROM productos");
$total_productos = $stmt->fetch()['total'] ?? 0;

// 2. Total Usuarios (no admins)
$stmt = $conn->query("SELECT COUNT(*) as total FROM usuarios WHERE id_rol = '2'");
$total_usuarios = $stmt->fetch()['total'] ?? 0;
```
- **Si te borran `$stmt->fetch()['total']`**: `$total_productos` quedaría como un objeto (o nulo) y no podrías imprimir el número en la tarjeta HTML. 
- La consulta de usuarios filtra por `id_rol = '2'` para no contar a los administradores en la estadística de clientes.

#### 3. Consulta de Productos Recientes (Tabla inferior)
```php
$stmt = $conn->query("
    SELECT p.id_producto, p.nombre, p.precio, p.activo, p.genero, c.nombre as categoria, s.nombre as subcategoria, i.url_imagen 
    FROM productos p 
    LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
    LEFT JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
    ORDER BY p.id_producto DESC LIMIT 4
");
$productos_recientes = $stmt->fetchAll();
```
- **Si te borran los `LEFT JOIN`**: Solo tendrías el ID de la categoría (ej. `1`), pero no el nombre (ej. `"Relojes"`). Los `LEFT JOIN` traducen los números de las tablas relacionales a nombres legibles.
- **Si te borran `ORDER BY ... DESC LIMIT 4`**: Traería TODOS los productos (posiblemente cientos) desde el más antiguo al más nuevo, destruyendo el propósito de la tabla "Productos Recientes".
