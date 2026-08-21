# Documentación: `models/Producto.php`

Este modelo se encarga de interactuar con la tabla `productos` en la base de datos. Sirve de puente entre los Controladores y los datos de los artículos (relojes, perfumes, accesorios).

### Explicación

#### 1. Estructura y Conexión
```php
class Producto {
    private PDO $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
}
```
- Se declara la clase `Producto`.
- En el constructor, captura la variable global `$conn` (que viene de `database.php`) y la guarda en la variable privada `$this->conn` para usarla en todos los métodos de este modelo.

#### 2. Método `obtenerPorCategoria($id_categoria)`
```php
public function obtenerPorCategoria(int $id_categoria) {
    $stmt = $this->conn->prepare("
        SELECT p.*, i.url_imagen 
        FROM productos p 
        LEFT JOIN imagen_producto i ON p.id_producto = i.id_producto AND i.principal = 1
        WHERE p.id_categoria = ? AND p.activo = 1
    ");
    $stmt->execute([$id_categoria]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```
- **Propósito:** Trae todos los productos de una categoría (ej. 1=Relojes) que estén activos (`p.activo = 1`).

- **Ejecución Segura:** Utiliza `prepare()` y `execute([$id_categoria])` (bind param) para prevenir inyecciones SQL si el `$id_categoria` fuera manipulado.

#### 3. Método `obtenerRecomendaciones($limite)`
```php
public function obtenerRecomendaciones(int $limite = 4) {
    $stmt = $this->conn->query("
        SELECT p.id_producto, p.nombre, p.precio, p.stock, p.url_imagen 
    FROM productos p
    WHERE p.activo = 1 AND p.stock > 0 
    ORDER BY RAND() LIMIT :limite
    ");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```
- **Propósito:** Se usa para el carrito de compras lateral. Devuelve productos aleatoriamente usando `ORDER BY RAND()` hasta un límite específico (por defecto 4).

#### 4. Método `descontarStock($id_producto, $cantidad)`
```php
public function descontarStock(int $id_producto, int $cantidad) {
    $stmt = $this->conn->prepare("UPDATE productos SET stock = stock - ? WHERE id_producto = ? AND stock >= ?");
    return $stmt->execute([$cantidad, $id_producto, $cantidad]);
}
```
- **Propósito:** Actualiza el inventario restando las unidades compradas.
- **Validación Lógica:** La consulta incluye `AND stock >= ?` como mecanismo de seguridad a nivel de base de datos para evitar que el inventario quede en números negativos (ej. si alguien compra 5 pero solo quedan 3 en la base de datos).
