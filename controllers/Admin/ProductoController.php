<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../../views/auth/login.php");
    exit;
}

require_once __DIR__ . '/../../models/Producto.php';

class ProductoController
{
    private Producto $productoModel;

    public function __construct()
    {
        $this->productoModel = new Producto();
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../views/admin/productos.php");
            exit;
        }

        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'referencia' => trim($_POST['referencia'] ?? ''),
            'id_categoria' => $_POST['id_categoria'] ?? '',
            'id_subcategoria' => !empty($_POST['id_subcategoria']) ? $_POST['id_subcategoria'] : null,
            'id_marca' => $_POST['id_marca'] ?? '',
            'genero' => $_POST['genero'] ?? 'Unisex',
            'precio' => $_POST['precio'] ?? 0,
            'stock' => $_POST['stock'] ?? 0,
            'descripcion' => trim($_POST['descripcion'] ?? '')
        ];
        
        $fecha_creacion = date('Y-m-d H:i:s');
        $ruta_imagen = '';
        $newFileName = '';

        // Procesar imagen
        if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['imagen_principal'];
            $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($ext, $allowed)) {
                $uploadFileDir = '../../public/img/productos/';
                if (!is_dir($uploadFileDir)) {
                    mkdir($uploadFileDir, 0755, true);
                }
                
                $newFileName = md5(time() . $file['name']) . '.' . $ext;
                $dest_path = $uploadFileDir . $newFileName;
                
                if (move_uploaded_file($file['tmp_name'], $dest_path)) {
                    $ruta_imagen = 'public/img/productos/' . $newFileName;
                }
            }
        }

        try {
            $this->productoModel->getConexion()->beginTransaction();
            
            $id_producto = $this->productoModel->insertar($datos, $fecha_creacion);
            
            if (!empty($ruta_imagen)) {
                $this->productoModel->insertarImagen($id_producto, $ruta_imagen, $newFileName, $fecha_creacion);
            }

            $this->productoModel->getConexion()->commit();
            header("Location: ../../views/admin/productos.php?exito=1");
            exit;
        } catch (PDOException $e) {
            if ($this->productoModel->getConexion()->inTransaction()) {
                $this->productoModel->getConexion()->rollBack();
            }
            error_log("Error guardando producto: " . $e->getMessage());
            header("Location: ../../views/admin/productos.php?error=1");
            exit;
        }
    }

    public function editar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../views/admin/productos.php");
            exit;
        }

        $id_producto = $_POST['id_producto'];
        $datos = [
            'nombre' => trim($_POST['nombre'] ?? ''),
            'referencia' => trim($_POST['referencia'] ?? ''),
            'id_categoria' => $_POST['id_categoria'] ?? '',
            'id_subcategoria' => !empty($_POST['id_subcategoria']) ? $_POST['id_subcategoria'] : null,
            'id_marca' => $_POST['id_marca'] ?? '',
            'genero' => $_POST['genero'] ?? 'Unisex',
            'precio' => $_POST['precio'] ?? 0,
            'stock' => $_POST['stock'] ?? 0,
            'descripcion' => trim($_POST['descripcion'] ?? '')
        ];

        try {
            $this->productoModel->getConexion()->beginTransaction();
            
            $this->productoModel->actualizar($id_producto, $datos);

            // Procesar imagen si se subió una nueva
            if (isset($_FILES['imagen_principal']) && $_FILES['imagen_principal']['error'] === UPLOAD_ERR_OK) {
                $file = $_FILES['imagen_principal'];
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $allowed = ['jpg', 'jpeg', 'png', 'webp'];
                
                if (in_array($ext, $allowed)) {
                    $new_name = uniqid('prod_') . '.' . $ext;
                    // En edición la ruta suele ser ../../img/productos/, pero usemos la misma que crear para consistencia si es public/img.
                    // Wait, original editar_producto_controller usaba '../../img/productos/' y guardaba 'img/productos/'.
                    // Ajustaremos a lo que usaba guardar_producto: 'public/img/productos/' para uniformar.
                    $upload_dir = '../../public/img/productos/';
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0755, true);
                    }
                    
                    $dest = $upload_dir . $new_name;
                    if (move_uploaded_file($file['tmp_name'], $dest)) {
                        $db_path = 'public/img/productos/' . $new_name;
                        $this->productoModel->actualizarImagen($id_producto, $db_path);
                    }
                }
            }

            $this->productoModel->getConexion()->commit();
            
            $_SESSION['alert'] = [
                'icon' => 'success',
                'title' => 'Producto Actualizado',
                'text' => 'Los cambios se han guardado correctamente.'
            ];
            header("Location: ../../views/admin/productos.php");
            exit;
        } catch (PDOException $e) {
            if ($this->productoModel->getConexion()->inTransaction()) {
                $this->productoModel->getConexion()->rollBack();
            }
            $_SESSION['alert'] = [
                'icon' => 'error',
                'title' => 'Error',
                'text' => 'Hubo un error: ' . $e->getMessage()
            ];
            header("Location: ../../controllers/Admin/ProductoController.php?accion=vista_editar&id=" . $id_producto);
            exit;
        }
    }

    public function toggleStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../views/admin/productos.php");
            exit;
        }

        $id_producto = $_POST['id_producto'] ?? null;
        $estado_actual = $_POST['estado_actual'] ?? 0;

        if ($id_producto) {
            $nuevo_estado = ($estado_actual == 1) ? 0 : 1;
            try {
                if ($this->productoModel->toggleEstado($id_producto, $nuevo_estado)) {
                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => '¡Éxito!',
                        'text' => 'El estado del producto ha sido actualizado.'
                    ];
                }
            } catch (PDOException $e) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Hubo un error al actualizar el producto.'
                ];
            }
        }
        header("Location: ../../views/admin/productos.php");
        exit;
    }

    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../views/admin/productos.php");
            exit;
        }

        $id_producto = (int)($_POST['id_producto'] ?? 0);
        
        if ($id_producto) {
            try {
                if ($this->productoModel->eliminar($id_producto)) {
                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => '¡Eliminado!',
                        'text' => 'El producto ha sido eliminado completamente.'
                    ];
                } else {
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Error',
                        'text' => 'No se pudo eliminar el producto.'
                    ];
                }
            } catch (Exception $e) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Ocurrió un error al eliminar el producto.'
                ];
            }
        }
        
        header("Location: ../../views/admin/productos.php");
        exit;
    }

    public function vistaEditar()
    {
        $id_producto = $_GET['id'] ?? null;
        if (!$id_producto) {
            header("Location: ../../views/admin/productos.php");
            exit;
        }

        try {
            $producto = $this->productoModel->obtenerPorId($id_producto);
            if (!$producto) {
                header("Location: ../../views/admin/productos.php");
                exit;
            }

            $categorias = $this->productoModel->obtenerCategorias();
            $marcas = $this->productoModel->obtenerMarcas();
            $marcas_cat = $this->productoModel->obtenerMarcasCategorias();

            // Incluir la vista y pasarle las variables
            require_once '../../views/admin/editar_producto.php';

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}

$controller = new ProductoController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'guardar':
        $controller->guardar();
        break;
    case 'editar':
        $controller->editar();
        break;
    case 'vista_editar':
        $controller->vistaEditar();
        break;
    case 'toggleStatus':
        $controller->toggleStatus();
        break;
    case 'eliminar':
        $controller->eliminar();
        break;
    default:
        header("Location: ../../views/admin/productos.php");
        break;
}
?>
