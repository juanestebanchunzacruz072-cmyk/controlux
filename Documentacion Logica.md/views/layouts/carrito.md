# Documentación: `views/layouts/carrito.php`

Este archivo es el panel lateral derecho que contiene el carrito de compras. Se incluye dinámicamente en todas las vistas públicas de la tienda.

### Explicación

#### 1. Generación de Recomendaciones
```php
<?php
require_once __DIR__ . '/../../models/Producto.php';
$productoModel = new Producto();
$recomendaciones = $productoModel->obtenerRecomendaciones(4);
?>
```
- **Propósito:** Al momento de cargar la página, PHP va a la base de datos y extrae 4 productos al azar utilizando el modelo `Producto`. Estos se imprimirán más abajo en la sección de "Te podría interesar".

#### 2. Javascript: Renderizado Dinámico del Carrito
```javascript
function renderCart() {
    let cart = JSON.parse(localStorage.getItem('controlux_cart')) || [];
    const cartItems = document.getElementById('cart-items');
    let total = 0;
    
    cartItems.innerHTML = '';
    
    if (cart.length === 0) {
        cartItems.innerHTML = '<div class="empty-cart-msg">Tu carrito está vacío</div>';
        ...
```
- **`localStorage`:** En lugar de guardar el carrito en la base de datos o en la sesión del servidor, se guarda en el navegador del cliente. Esto hace que la página sea ultrarrápida y no consuma recursos del servidor por cada clic en "Añadir".
- **`renderCart()`:** Esta función lee el arreglo de productos, calcula el `$total` y dibuja el HTML de cada artículo (con su foto, nombre y precio) directamente en el panel sin recargar.

#### 3. Javascript: Finalizar Compra (Checkout)
```javascript
function finalizarCompra() {
    let cart = JSON.parse(localStorage.getItem('controlux_cart')) || [];
    ...
    fetch('/controlux/controllers/cliente/CarritoController.php?accion=guardar', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(cart)
    })
    .then(response => response.json())
    .then(data => {
        if (data.status === 'logged_in' || data.status === 'not_logged') {
            window.location.href = data.redirect;
        }
    });
}
```
- **Propósito:** Es el puente entre el frontend y el backend. Cuando el cliente hace clic en pagar, Javascript toma todos los productos del `localStorage` y se los lanza por la cabeza al `CarritoController.php` usando `fetch`.
- Luego, obedece la respuesta del servidor (el `redirect`), enviando al usuario al panel de pago final o al login si no había iniciado sesión.
