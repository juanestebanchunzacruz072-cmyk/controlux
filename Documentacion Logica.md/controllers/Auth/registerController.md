# Documentación: `controllers/Auth/registerController.php`

Este controlador procesa el registro de un nuevo usuario cliente desde el formulario de la tienda, validando y guardando los datos personales y de envío de manera asíncrona.

### Explicación

#### 1. Recepción y Validación de Datos Asíncrona (JSON)
El controlador recibe los datos y debe responder en formato `JSON` porque el formulario original utiliza `fetch()` para no recargar la página.

```php
header('Content-Type: application/json');

$nombre = trim($_POST['nombre'] ?? '');
$correo = trim($_POST['correo'] ?? '');
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
...
```
- Configura la cabecera `Content-Type` para que Javascript entienda la respuesta.
- Extrae todas las variables de envío (Cédula, Dirección, Barrio, Ciudad, Teléfono) y aplica limpieza con `trim()`.

#### 2. Comprobación de Contraseñas
```php
if ($password !== $confirm_password) {
    echo json_encode([
        'status' => 'error',
        'title' => 'Contraseñas no coinciden',
        'message' => 'Asegúrese de escribir la misma contraseña.'
    ]);
    exit;
}
```
- Regla básica de seguridad y experiencia de usuario. Si las contraseñas son diferentes, aborta el proceso devolviendo el `JSON` correspondiente para que se muestre el error en pantalla.

#### 3. Verificación de Usuario Existente
```php
$usuarioModel = new Usuario();
if ($usuarioModel->emailExiste($correo)) {
    echo json_encode([
        'status' => 'error',
        'title' => 'Correo Registrado',
        'message' => 'Este correo electrónico ya se encuentra registrado.'
    ]);
    exit;
}
```
- Llama al modelo `Usuario` y usa la función `emailExiste()`. Esto garantiza la unicidad de las cuentas en la base de datos.

#### 4. Registro y Asignación Automática de Rol
```php
$id_rol = 2; // Cliente
$registrado = $usuarioModel->registrar(
    $nombre, $apellido, $correo, $password, 
    $telefono, $direccion, $ciudad, $barrio, $cedula, $id_rol
);

if ($registrado) {
    echo json_encode([
        'status' => 'success',
        'title' => 'Registro Exitoso',
        'message' => 'Tu cuenta ha sido creada correctamente.',
        'redirect' => 'login.php'
    ]);
}
```
- Por defecto, toda persona que se registra por este medio obtiene el `$id_rol = 2` (que pertenece a Cliente en la tabla de roles). Los administradores (`$id_rol = 1`) deben ser asignados de otra manera.
- Si el modelo retorna `true`, devuelve un `success` indicando al Frontend la ruta a la cual debe redirigir (`login.php`).
