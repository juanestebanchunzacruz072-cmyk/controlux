<?php
session_start();

class CarritoController
{
    public function guardar()
    {
        header('Content-Type: application/json');
        
        $data = json_decode(file_get_contents('php://input'), true);

        if ($data && is_array($data)) {
            $_SESSION['carrito_temporal'] = $data;
            
            if (isset($_SESSION['id_usuario'])) {
                echo json_encode([
                    "status" => "logged_in",
                    "redirect" => "/controlux/views/cliente/detalle_pedido.php"
                ]);
            } else {
                echo json_encode([
                    "status" => "not_logged",
                    "redirect" => "/controlux/views/auth/login.php"
                ]);
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Datos inválidos"]);
        }
    }

    public function limpiar()
    {
        if (isset($_SESSION['carrito_temporal'])) {
            unset($_SESSION['carrito_temporal']);
        }
        header('Location: ../../public/index.php');
        exit;
    }
}

$controller = new CarritoController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'guardar':
        $controller->guardar();
        break;
    case 'limpiar':
        $controller->limpiar();
        break;
    default:
        http_response_code(400);
        echo json_encode(['error' => 'Acción no válida']);
        break;
}
?>
