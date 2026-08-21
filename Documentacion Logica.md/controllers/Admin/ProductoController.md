# Documentación: `controllers/Admin/ProductoController.php`

Este controlador gestiona toda la lógica del administrador para agregar, editar y eliminar productos de la tienda, incluyendo la subida de imágenes.

### Explicación

#### 1. Protección de Acceso
```php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../../views/auth/login.php");
    exit;
}
```
- **Seguridad Administrador:** Antes de cargar cualquier función, el controlador verifica que el usuario esté logueado y que su `id_rol` sea exactamente `1` (Administrador). De lo contrario, lo expulsa al login para evitar accesos no autorizados mediante URL.

#### 2. Subida de Imagen Principal (Método `guardar`)
```php
if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
    $file = $_FILES['imagen_principal'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp'];
    
    if (in_array($ext, $allowed)) {
        $uploadFileDir = '../../public/img/productos/';
        ...
        $newFileName = md5(time() . $file['name']) . '.' . $ext;
        $dest_path = $uploadFileDir . $newFileName;
        
        if (move_uploaded_file($file['tmp_name'], $dest_path)) {
            $ruta_imagen = 'public/img/productos/' . $newFileName;
        }
    }
}
```
- **Propósito:** Lee la foto subida desde el formulario (`$_FILES`), verifica que sea un formato de imagen permitido (JPG, PNG, WEBP), y crea un nombre único encriptando el nombre original junto con la hora actual (`md5(time()...)`) para que nunca haya dos fotos llamadas igual que se sobrescriban. Finalmente mueve el archivo a la carpeta pública.

#### 3. Transacción SQL: Producto + Imagen
```php
try {
    $this->productoModel->getConexion()->beginTransaction();
    
    $id_producto = $this->productoModel->insertar($datos, $fecha_creacion);
    
    if (!empty($ruta_imagen)) {
        $this->productoModel->insertarImagen($id_producto, $ruta_imagen, $newFileName, $fecha_creacion);
    }

    $this->productoModel->getConexion()->commit();
    ...
} catch (PDOException $e) {
    if ($this->productoModel->getConexion()->inTransaction()) {
        $this->productoModel->getConexion()->rollBack();
    }
    ...
}
```
- Al igual que en la compra de clientes, la creación de un producto utiliza `beginTransaction()`. Esto asegura que si falla la inserción de la imagen en su respectiva tabla, no quede creado un producto fantasma sin foto en la base de datos principal (`rollBack()`).
