# Documentación: `controllers/cliente/CarritoController.php`

Este controlador actúa como puente entre el `localStorage` (donde se guarda el carrito temporalmente sin necesidad de conexión) y el inicio del proceso formal de checkout (pago) cuando el usuario decide finalizar su compra.

### Explicación

#### 1. Recepción del Carrito (`guardar()`)
```php
$data = json_decode(file_get_contents('php://input'), true);

if ($data && is_array($data)) {
    $_SESSION['carrito_temporal'] = $data;
```
- **Recepción JSON:** Como los datos vienen de `localStorage` enviados vía `fetch()`, PHP no los recibe por el método tradicional `$_POST`. Usa `php://input` y `json_decode()` para transformarlos en un arreglo de PHP.
- **Guardado en Sesión:** Inyecta todos los productos (id, nombre, precio, cantidad) dentro de `$_SESSION['carrito_temporal']`. De esta forma, el servidor "se acuerda" de qué iba a comprar el usuario, y lo podrá leer en la siguiente pantalla (detalle de pedido).

#### 2. Lógica de Redirección Inteligente
```php
if (isset($_SESSION['id_usuario'])) {
    echo json_encode([
        "status" => "logged_in",
        "redirect" => "/controlux/views/cliente/detalle_pedido.php"
    ]);
} else {
    echo json_encode([
        "status" => "not_logged",
        "redirect" => "/controlux/views/auth/login.php"
    ]);
}
```
- Valida si el cliente tiene una cuenta conectada al sistema en el momento en que intentó comprar.
- Si está logueado, le devuelve a Javascript la URL de la página de confirmación final de pago (`detalle_pedido.php`).
- Si **no** está logueado, le devuelve la ruta de `login.php`. Como los artículos ya se guardaron en la sesión en el paso anterior, al iniciar sesión el usuario no perderá su carrito.

#### 3. Método `limpiar()`
```php
public function limpiar() {
    if (isset($_SESSION['carrito_temporal'])) {
        unset($_SESSION['carrito_temporal']);
    }
    header('Location: ../../public/index.php');
    exit;
}
```
- **Propósito:** Destruye el arreglo del carrito en la sesión (vacía el carrito a nivel de backend) y devuelve al usuario a la página principal.

#### 4. Switch Controlador (Enrutador)
```php
$accion = $_GET['accion'] ?? '';
switch ($accion) {
    case 'guardar':
        $controller->guardar();
        break;
    ...
```
- Captura la acción solicitada por URL (ej: `CarritoController.php?accion=guardar`) y ejecuta el método de la clase correspondiente.
