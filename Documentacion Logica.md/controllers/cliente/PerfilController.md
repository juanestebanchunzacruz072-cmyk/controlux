# Documentación: `controllers/cliente/PerfilController.php`

Este controlador permite que un cliente que ya inició sesión pueda actualizar sus datos de envío (dirección, ciudad, barrio) desde su panel personal.

### Explicación

#### 1. Protección de Acceso
```php
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}
```
- Verifica que nadie pueda actualizar un perfil si no tiene una sesión de usuario activa.

#### 2. Recepción de Nuevos Datos
```php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    
    // Solo permitimos actualizar dirección y ciudad
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $barrio = trim($_POST['barrio'] ?? '');
    ...
```
- Lee los datos enviados desde el formulario `perfil.php`. Fíjate que el controlador por seguridad **lee el ID del usuario directamente de la sesión** (`$_SESSION['id_usuario']`) y no de un campo oculto del formulario. Esto impide que un cliente malicioso cambie el ID en el HTML para modificar los datos de otra persona.

#### 3. Actualización SQL Directa
```php
    try {
        $stmt = $conn->prepare("UPDATE usuarios SET direccion = ?, ciudad = ?, barrio = ? WHERE id_usuario = ?");
        if ($stmt->execute([$direccion, $ciudad, $barrio, $id_usuario])) {
            header("Location: ../../views/cliente/perfil.php?exito=1");
            exit;
        } else {
            ...
```
- Utiliza una consulta `UPDATE` directa usando PDO (`prepare` + `execute`). 
- **Gestión de Respuestas:** En lugar de lanzar una variable de sesión para las alertas, este controlador devuelve al usuario a la vista agregando un parámetro en la URL (`?exito=1` o `?error=1`). El Javascript en la vista de perfil leerá esa URL para disparar el mensaje de éxito o de error.
