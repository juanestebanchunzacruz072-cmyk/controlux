<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../../views/auth/login.php");
    exit;
}

require_once __DIR__ . '/../../models/Marca.php';

class MarcaController
{
    private Marca $marcaModel;

    public function __construct()
    {
        $this->marcaModel = new Marca();
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../views/admin/agregar_marca.php");
            exit;
        }

        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $categorias = $_POST['categorias'] ?? [];
        $subcategorias = $_POST['subcategorias'] ?? [];

        if (empty($nombre) || empty($categorias)) {
            header("Location: ../../views/admin/agregar_marca.php?error=1");
            exit;
        }

        $id_marca = $this->marcaModel->insertar($nombre, $descripcion, $categorias, $subcategorias);

        if ($id_marca) {
            header("Location: ../../views/admin/agregar_marca.php?exito=1");
        } else {
            header("Location: ../../views/admin/agregar_marca.php?error=1");
        }
        exit;
    }

    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../views/admin/agregar_marca.php");
            exit;
        }

        $id_marca = (int)($_POST['id_marca'] ?? 0);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $categorias = $_POST['categorias'] ?? [];
        $subcategorias = $_POST['subcategorias'] ?? [];

        if (empty($id_marca) || empty($nombre) || empty($categorias)) {
            header("Location: ../../views/admin/agregar_marca.php?error_act=1");
            exit;
        }

        $exito = $this->marcaModel->actualizar($id_marca, $nombre, $descripcion, $categorias, $subcategorias);
        if ($exito) {
            header("Location: ../../views/admin/agregar_marca.php?exito_act=1");
        } else {
            header("Location: ../../views/admin/agregar_marca.php?error_act=1");
        }
        exit;
    }

    public function cambiarEstado(int $estado)
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $this->marcaModel->cambiarEstado($id, $estado);
        }
        header("Location: ../../views/admin/agregar_marca.php");
        exit;
    }
    public function eliminar()
    {
        $id = (int)($_GET['id'] ?? 0);
        if ($id) {
            $exito = $this->marcaModel->eliminar($id);
            if ($exito) {
                header("Location: ../../views/admin/agregar_marca.php?exito_eliminar=1");
                exit;
            }
        }
        header("Location: ../../views/admin/agregar_marca.php?error_eliminar=1");
        exit;
    }
}

$controller = new MarcaController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'guardar':
        $controller->guardar();
        break;
    case 'actualizar':
        $controller->actualizar();
        break;
    case 'ocultar':
        $controller->cambiarEstado(0);
        break;
    case 'activar':
        $controller->cambiarEstado(1);
        break;
    case 'eliminar':
        $controller->eliminar();
        break;
    default:
        header("Location: ../../views/admin/dashboard_admin.php");
        break;
}
?>
