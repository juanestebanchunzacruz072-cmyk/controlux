# Documentación: `models/rol.php`

Este modelo sirve de soporte para el panel de administración, interactuando con la tabla `roles`.

### Explicación

#### 1. Obtener Roles (`obtenerTodos()`)
```php
public function obtenerTodos()
{
    $query = 'SELECT id_rol, nombre AS rol FROM roles';
    $stmt = $this->conn->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll();
}
```
- **Propósito:** Trae el catálogo de roles disponibles (Administrador, Cliente). 
- **Uso:** Esto se emplea usualmente en el formulario de editar/crear usuarios dentro del panel de administración (`dashboard_admin.php`), para llenar la lista desplegable (`<select>`) donde el administrador elige el nivel de acceso que le va a dar a otra persona.
