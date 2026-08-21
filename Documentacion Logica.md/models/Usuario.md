# Documentación: `models/Usuario.php`

Este modelo gestiona todo lo referente a los clientes y administradores (registro, consulta, y actualización en la tabla `usuarios`).

### Explicación

#### 1. Método `registrar(...)`
```php
public function registrar(string $nombre, string $apellido, string $correo, string $password, ...) {
    try {
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);
        $query = 'INSERT INTO usuarios (id_rol, nombre, apellido, cedula, correo, password, telefono, direccion, ciudad, barrio, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id_rol, $nombre, $apellido, $cedula, $correo, $hashed_password, $telefono, $direccion, $ciudad, $barrio, $activo]);
    } catch (PDOException $e) {
        ...
    }
}
```
- **Propósito:** Crea un nuevo usuario en la base de datos.
- **Seguridad:** Usa `password_hash($password, PASSWORD_BCRYPT)` para encriptar la contraseña de manera irreversible antes de guardarla.
- **Roles:** Recibe el parámetro `$id_rol` (con valor por defecto `2` que es Cliente).

#### 2. Método `emailExiste($email)`
```php
public function emailExiste(string $email) {
    $query = 'SELECT id_usuario FROM usuarios WHERE correo = ?';
    $stmt = $this->conn->prepare($query);
    $stmt->execute([$email]);
    return $stmt->rowCount() > 0;
}
```
- **Propósito:** Validar si un correo ya se encuentra registrado. Se usa en el registro para evitar cuentas duplicadas. Usa `rowCount() > 0` devolviendo `true` si encuentra el correo.

#### 3. Método `obtenerPorEmail($email)`
```php
public function obtenerPorEmail(string $email) {
    $query = 'SELECT id_usuario, nombre AS usuario, correo, password, id_rol, activo FROM usuarios WHERE correo = ?';
    ...
    return $stmt->fetch();
}
```
- **Propósito:** Trae toda la información de un usuario dado su correo. Es el método central utilizado en el **login** para comparar la contraseña ingresada con la contraseña encriptada de la base de datos.

#### 4. Gestión de Usuarios (Panel Admin)
```php
public function actualizar(int $id_usuario, array $datos) {
    ...
    $query = 'UPDATE usuarios SET nombre = ?, id_rol = ?, activo = ?';
    ...
    if (!empty($datos['email'])) {
        $query .= ', correo = ?';
        ...
    }
    ...
}
```
- **Propósito:** Actualiza los datos de un cliente o administrador desde el panel de administración. Construye la consulta SQL de manera dinámica (solo añade el correo a la actualización si el administrador lo rellenó).
