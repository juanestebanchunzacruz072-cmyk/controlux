<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    http_response_code(403);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

require_once __DIR__ . '/../../models/Subcategoria.php';

class SubcategoriaController
{
    private Subcategoria $subcategoriaModel;

    public function __construct()
    {
        $this->subcategoriaModel = new Subcategoria();
    }

    public function obtenerPorCategoria()
    {
        header('Content-Type: application/json');
        
        $id_categoria = isset($_GET['id_categoria']) ? intval($_GET['id_categoria']) : 0;
        
        if ($id_categoria > 0) {
            try {
                $subcategorias = $this->subcategoriaModel->obtenerPorCategoria($id_categoria);
                echo json_encode($subcategorias);
            } catch (PDOException $e) {
                http_response_code(500);
                echo json_encode(['error' => 'Error en la base de datos']);
            }
        } else {
            echo json_encode([]);
        }
    }
}

$controller = new SubcategoriaController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'obtenerPorCategoria':
        $controller->obtenerPorCategoria();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
