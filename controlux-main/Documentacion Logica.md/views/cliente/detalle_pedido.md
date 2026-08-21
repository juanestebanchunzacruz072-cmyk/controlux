# Documentación: `views/cliente/detalle_pedido.php`

Esta es la vista donde el cliente ve el resumen final de lo que va a comprar antes de confirmar su pedido y ser enviado a WhatsApp. Al ser una vista con lógica transaccional, contiene bloques PHP muy importantes que suelen ser objetivo de evaluación.

### Explicación

#### 1. Doble Validación de Seguridad Inicial
```php
<?php
session_start();

// Si no hay carrito, volver al index
if (!isset($_SESSION['carrito_temporal']) || empty($_SESSION['carrito_temporal'])) {
    header("Location: ../../public/index.php");
    exit;
}

// Si no está logueado, mandarlo al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}
```
- **Si te borran el primer `if`:** Un usuario podría escribir `detalle_pedido.php` manualmente en la URL sin haber agregado nada al carrito. La página intentaría calcular un total de algo que no existe y probablemente arrojaría errores de *Warning: foreach() argument must be of type array or object*.
- **Si te borran el segundo `if`:** Un visitante anónimo podría llegar hasta el botón "Confirmar Pedido". Cuando hiciera clic, el controlador de pedidos intentaría guardar la compra en la base de datos sin un `$id_usuario` válido, lo que causaría un error fatal en la base de datos (restricción de clave foránea).

#### 2. Cálculo Matemático del Subtotal
```php
$carrito = $_SESSION['carrito_temporal'];
$subtotal = 0;
foreach ($carrito as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal;
```
- **Propósito:** Recorre cada producto que el `CarritoController` guardó previamente en la sesión.
- **Si te borran la línea `$subtotal += ...`:** Debes recordar la lógica comercial básica: el subtotal de un ítem es su `precio` multiplicado por su `cantidad`. El operador `+=` va sumando ese resultado al gran total general. Si falta esto, el pedido siempre saldría gratis ($0).

#### 3. Impresión Segura de Datos (Prevención XSS)
```html
<div class="text-white">
    <i class="bi bi-person-circle"></i> 
    <?php echo htmlspecialchars($_SESSION['usuario']['usuario'] ?? 'Cliente'); ?>
</div>
```
- **Si te borran `htmlspecialchars(...)` y dejan solo `echo $_SESSION...`:** Aunque en este caso es el nombre del usuario, usar `htmlspecialchars` es una buena práctica de seguridad que los profesores evalúan. Evita ataques XSS (Cross-Site Scripting) transformando cualquier código HTML malicioso que el usuario haya puesto en su nombre (ej: `<script>alert(1)</script>`) en texto inofensivo.
