# Documentación: `controllers/cliente/PedidoClienteController.php`

Este controlador es el encargado de procesar la finalización de compra de un cliente y redirigirlo a WhatsApp con los datos generados.

### Explicación

#### 1. Instanciación y Preparación (Líneas iniciales)
```php
session_start();   
require_once '../../config/database.php';
require_once '../../models/Pedido.php';
...
```
Inicia la sesión (para saber quién es el cliente) y requiere los modelos necesarios.

#### 2. Método `guardar()`: Validaciones de Sesión
```php
if (!isset($_SESSION['id_usuario']) || empty($_SESSION['carrito_temporal'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}
```
- Se asegura de que el usuario haya iniciado sesión y que el carrito (almacenado en `$_SESSION['carrito_temporal']`) no esté vacío. Si alguna falla, lo devuelve al login.

#### 3. Transacción SQL (Try-Catch)
```php
$this->pedidoModel->getConexion()->beginTransaction();
try {
    ...
    $this->pedidoModel->getConexion()->commit();
} catch (PDOException $e) {
    $this->pedidoModel->getConexion()->rollBack();
}
```
- **`beginTransaction()`:** Inicia una transacción. Esto asegura que si hay un error (por ejemplo, al insertar el detalle 3 de 5), la base de datos revierta todos los cambios anteriores (`rollBack()`) y no queden "pedidos fantasma". Si todo sale bien, guarda permanentemente con `commit()`.

#### 4. Inserción del Pedido y Detalles
```php
$id_pedido = $this->pedidoModel->insertarPedido($id_usuario, $total);
```
- Registra la cabecera del pedido en la BD y recupera su `$id_pedido`.

```php
foreach ($_SESSION['carrito_temporal'] as $item) {
    ...
    $this->pedidoModel->insertarDetalle($id_pedido, $id_producto, $cantidad, $precio, $subtotal_item);
    $this->productoModel->descontarStock($id_producto, $cantidad);
}
```
- Itera sobre cada elemento que el cliente llevaba en el carrito, inserta su detalle en la tabla `detalle_pedidos` y llama a `descontarStock()` para reducir el inventario físico.

#### 5. Construcción del Mensaje para WhatsApp
```php
$mensaje = "Hola JC URBAN, quiero hacer un pedido:\n";
$mensaje .= "Cliente: " . $usuario['nombre'] . "\n";
...
$resumen_texto = urlencode($mensaje);
header("Location: https://wa.me/$numero_wa?text=$resumen_texto");
```
- Utiliza la información del cliente extraída previamente para armar un bloque de texto que será amigable para el asesor de ventas.
- Usa `urlencode()` para que espacios y saltos de línea (`\n`) sean legibles al pasarse por URL.
- Finalmente, se ejecuta el `header("Location: ...")` que automáticamente envía al cliente de la tienda web a la aplicación de WhatsApp de JC URBAN.
