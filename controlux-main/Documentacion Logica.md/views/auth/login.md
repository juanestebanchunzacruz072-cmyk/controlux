# Documentación: `views/auth/login.php`

Esta es la interfaz gráfica donde los usuarios (clientes y administradores) introducen sus credenciales para acceder al sistema.

### Explicación Código por Código

#### 1. Protección de Sesión Activa
```php
<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    if ($_SESSION['usuario']['id_rol'] == '1') {
        header("Location: ../admin/dashboard_admin.php");
        exit;
    }
    header("Location: ../../public/index.php");
    exit;
}
?>
```
- **Propósito:** Previene que un usuario que ya inició sesión vuelva a ver el formulario de login. Si intenta entrar, PHP detecta su sesión y lo expulsa automáticamente hacia el panel de administración (si es rol 1) o hacia la tienda pública (si es cliente).

#### 2. Formulario HTML
```html
<form action="../../controllers/Auth/authController.php" method="POST">
    <div class="input-group">
        <label>Correo Electrónico</label>
        <input type="email" name="correo" required>
    </div>
    ...
```
- **Estructura:** Un formulario HTML clásico que captura `correo` y `password`. Al hacer click en "Ingresar", los datos viajan por método `POST` directo hacia el `authController.php` documentado anteriormente.

#### 3. Interceptación de Alertas (SweetAlert)
```php
<?php
if (isset($_SESSION['alert'])) {
    $alert = $_SESSION['alert'];
    echo "<script>
        Swal.fire({
            icon: '{$alert['icon']}',
            title: '{$alert['title']}',
            text: '{$alert['text']}'
        });
    </script>";
    unset($_SESSION['alert']);
}
?>
```
- **Comunicación con el Controlador:** Si el controlador de Auth detecta que la contraseña es incorrecta, crea una variable `$_SESSION['alert']`. Cuando el login recarga la página, este bloque de PHP detecta la alerta, imprime el código Javascript para que salte el pop-up en pantalla, y luego destruye la variable (`unset`) para que la alerta no vuelva a salir si se refresca la página.
