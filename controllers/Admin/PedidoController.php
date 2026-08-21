<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../../views/auth/login.php");
    exit;
}

require_once __DIR__ . '/../../models/Pedido.php';

class PedidoController
{
    private Pedido $pedidoModel;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
    }

    public function cambiarEstado()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../views/admin/pedidos.php");
            exit;
        }

        $id_pedido = $_POST['id_pedido'] ?? null;
        $id_estado = $_POST['id_estado'] ?? null;

        if ($id_pedido && $id_estado) {
            try {
                if ($this->pedidoModel->cambiarEstado($id_pedido, $id_estado)) {
                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => 'Estado Actualizado',
                        'text' => 'El pedido #' . str_pad($id_pedido, 5, '0', STR_PAD_LEFT) . ' ha sido actualizado exitosamente.'
                    ];
                }
            } catch (PDOException $e) {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Error',
                    'text' => 'Hubo un error al actualizar el estado.'
                ];
            }
        }
        
        header("Location: ../../views/admin/pedidos.php");
        exit;
    }
}

$controller = new PedidoController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'cambiarEstado':
        $controller->cambiarEstado();
        break;
    default:
        header("Location: ../../views/admin/pedidos.php");
        break;
}
?>
