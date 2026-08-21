# Documentación: `models/Pedido.php`

Este modelo se encarga de interactuar con la tabla `pedidos` y `detalle_pedidos` en la base de datos. Sirve para gestionar todo lo relacionado con las órdenes de compra.

### Explicación

#### 1. Estructura y Conexión
```php
class Pedido {
    private PDO $conn;

    public function __construct() {
        global $conn;
        $this->conn = $conn;
    }
}
```
- Se declara la clase `Pedido`.
- En el constructor, captura la conexión a la base de datos `$conn` y la almacena para su uso en los siguientes métodos.

#### 2. Método `insertarPedido($id_usuario, $total)`
```php
public function insertarPedido(int $id_usuario, float $total) {
    $stmt = $this->conn->prepare("INSERT INTO pedidos (id_usuario, fecha_pedido, subtotal, total, id_estado) VALUES (?, NOW(), ?, ?, 1)");
    $stmt->execute([$id_usuario, $total, $total]);
    return $this->conn->lastInsertId();
}
```
- **Propósito:** Crea el encabezado principal del pedido. Asocia el pedido al usuario y guarda el total a pagar.
- **`NOW()` y Estado:** Inserta automáticamente la fecha actual (`NOW()`) y el estado por defecto `1` (que suele significar "Pendiente").
- **Retorno Clave:** Usa `lastInsertId()` para devolver el ID del pedido recién creado. Esto es vital para poder insertar los detalles del pedido a continuación.

#### 3. Método `insertarDetalle(...)`
```php
public function insertarDetalle(int $id_pedido, int $id_producto, int $cantidad, float $precio, float $subtotal) {
    $stmt = $this->conn->prepare("INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    return $stmt->execute([$id_pedido, $id_producto, $cantidad, $precio, $subtotal]);
}
```
- **Propósito:** Guarda cada producto individual (reloj, perfume, etc.) dentro del pedido maestro. Relaciona el `$id_pedido` con el `$id_producto`.

#### 4. Método `cambiarEstado($id_pedido, $id_estado)`
```php
public function cambiarEstado(int $id_pedido, int $id_estado) {
    $stmt = $this->conn->prepare("UPDATE pedidos SET id_estado = :id_estado WHERE id_pedido = :id_pedido");
    $stmt->bindParam(':id_estado', $id_estado, PDO::PARAM_INT);
    $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
    return $stmt->execute();
}
```
- **Propósito:** Permite a los administradores actualizar el progreso de la compra (ej. de Pendiente a Enviado o Entregado).
- Usa `bindParam` para enlazar las variables de forma segura como enteros (`PDO::PARAM_INT`).
