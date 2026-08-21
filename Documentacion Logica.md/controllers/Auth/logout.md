# Documentación: `controllers/Auth/logout.php`

Este archivo se encarga de cerrar de forma segura la sesión de un usuario (sea cliente o administrador).

### Explicación

#### 1. Cierre de Sesión Seguro
```php
<?php
session_start();
session_unset();
session_destroy();
header('Location: ../../public/index.php');
exit;
?>
```
- **`session_start()`**: Reanuda la sesión actual para poder manipularla.
- **`session_unset()`**: Elimina todas las variables registradas dentro de `$_SESSION` (como el ID de usuario o el carrito si no se había pagado).
- **`session_destroy()`**: Destruye la sesión por completo del servidor.
- **Redirección:** Envía al usuario de vuelta a la página principal (`index.php`) de la tienda como un visitante anónimo.
