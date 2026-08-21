# Documentación: `models/Subcategoria.php`

Este modelo sirve de soporte para el catálogo, permitiendo agrupar y filtrar productos dentro de nichos específicos (por ejemplo, "Smartwatches" dentro de la categoría general "Relojes").

### Explicación

#### 1. Consulta Filtrada (`obtenerPorCategoria()`)
```php
public function obtenerPorCategoria(int $id_categoria)
{
    $stmt = $this->conn->prepare("SELECT id_subcategoria, nombre FROM subcategoria WHERE id_categoria = :id_categoria ORDER BY nombre ASC");
    $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
```
- **Propósito:** Trae de la base de datos la lista de subcategorías, pero **solamente** aquellas que pertenezcan a la categoría padre solicitada (`id_categoria`).
- **Formato:** Usa `ORDER BY nombre ASC` para devolver la lista ordenada alfabéticamente (A-Z) para que luzca bien en los filtros de la tienda.
- **Uso Común:** Se emplea en las vistas públicas (`relojes.php`, `perfumes.php`) para dibujar los menús laterales de filtros, y en el panel de administrador para que, cuando el admin vaya a subir un producto y seleccione "Relojes", otro campo despliegue automáticamente sus subcategorías relacionadas.
