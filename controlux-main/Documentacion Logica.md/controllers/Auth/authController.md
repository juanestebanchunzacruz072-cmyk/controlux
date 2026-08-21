# Documentación: `controllers/Auth/authController.php`

Este controlador gestiona el inicio de sesión (`login`) de los usuarios y administradores, verificando sus credenciales contra la base de datos.

### Explicación

#### 1. Validación de la Petición
```php
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../../views/auth/login.php');
    exit;
}
```
- Se asegura de que los datos hayan sido enviados a través del formulario de login (`POST`). Si un usuario intenta acceder a la URL del controlador directamente por el navegador (vía `GET`), es devuelto a la vista de login.

#### 2. Limpieza y Validación Básica
```php
$correo = trim($_POST['correo'] ?? '');
$password = trim($_POST['password'] ?? '');

if (empty($correo) || empty($password)) {
    $_SESSION['alert'] = [
        'icon' => 'warning',
        'title' => 'Campos incompletos',
        'text' => 'Debe ingresar correo y contraseña'
    ];
    header('Location: ../../views/auth/login.php');
    exit;
}
```
- Utiliza `trim()` para eliminar espacios en blanco accidentales que el usuario haya puesto al principio o final.
- Si los campos están vacíos, crea una variable de sesión `$_SESSION['alert']` que será leída por la vista para mostrar un *SweetAlert* de advertencia.

#### 3. Validación de Correo
```php
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['alert'] = [ ... ];
    header('Location: ../../views/auth/login.php');
    exit;
}
```
- Emplea la función nativa `filter_var` con el flag `FILTER_VALIDATE_EMAIL` para comprobar que el texto introducido tenga el formato correcto (`texto@dominio.com`), previniendo errores en la consulta de BD.

#### 4. Consulta a la Base de Datos
```php
$usuarioModel = new Usuario();
$usuario = $usuarioModel->obtenerPorEmail($correo);
```
- Instancia el modelo `Usuario` y utiliza la función `obtenerPorEmail` pasándole el correo. Si el correo no existe en la base de datos, `$usuario` será `false`.

#### 5. Verificación de Contraseña y Estado
*(Siguientes pasos lógicos dentro del controlador)*
- Se comprueba si `$usuario` devolvió un registro válido.
- Se comprueba que el usuario esté activo (`activo == 1`).
- Se utiliza `password_verify($password, $usuario['password'])` para comprobar matemáticamente que la clave digitada corresponda con el *hash* encriptado que está guardado en la base de datos.
- Si todo es correcto, se inicia la sesión y se redirige al panel de administrador o al inicio de la tienda dependiendo del `id_rol`.
