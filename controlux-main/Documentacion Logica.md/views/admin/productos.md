# Documentación: `views/admin/productos.php`

Esta vista muestra el catálogo completo en formato de tabla para el administrador, permitiendo filtrar por Categoría, Subcategoría y Marca.

### Explicación

#### 1. Captura de Filtros (Método GET)
```php
$filtro_categoria = $_GET['id_categoria'] ?? '';
$filtro_subcategoria = $_GET['id_subcategoria'] ?? '';
$filtro_marca = $_GET['id_marca'] ?? '';
```
- **Propósito:** Lee la URL del navegador (ej: `productos.php?id_categoria=1`). 
- **Si te borran el `?? ''` (Null coalescing operator):** PHP lanzaría un error *Notice: Undefined index* si el administrador entra a la página por primera vez sin haber hecho clic en ningún filtro.

#### 2. Carga de Listas Desplegables
```php
$categorias_lista = $conn->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);
```
- **Propósito:** Llena las etiquetas `<select>` del filtro en el HTML. Se usa `PDO::FETCH_ASSOC` para que el resultado sea un arreglo limpio (`['id_categoria' => 1, 'nombre' => 'Relojes']`) en lugar de duplicar los datos con índices numéricos.

#### 3. Construcción Dinámica de la Consulta SQL
Esta es la parte más compleja que un profesor podría evaluar. Dependiendo de si seleccionaste filtros o no, el query (`WHERE 1=1`) crece dinámicamente.

```php
$sql_productos = "SELECT p.*, c.nombre as categoria ... FROM productos p ... WHERE 1=1";
$params = [];

if (!empty($filtro_categoria)) {
    $sql_productos .= " AND p.id_categoria = :id_categoria";
    $params[':id_categoria'] = $filtro_categoria;
}
// Repite para subcategoria y marca
$sql_productos .= " ORDER BY p.id_producto DESC";

$stmt = $conn->prepare($sql_productos);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
```
- **Si te borran `WHERE 1=1`:** La concatenación de filtros fallaría. Si intentas pegar `AND p.id_categoria = 1` justo después del `FROM`, la sintaxis SQL se rompe porque falta la palabra `WHERE`. El `1=1` (que siempre es verdadero) sirve como ancla para ir pegando `ANDs` sin preocuparse por cuál es el primer filtro.
- **Uso de Parámetros (`:id_categoria`)**: El arreglo `$params` acumula los valores y se los pasa al `execute()`. Si te borran el `prepare` y lo cambian por `query`, el sistema sería vulnerable a inyecciones SQL a través de la URL.
