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
                $estado_actual = $this->pedidoModel->obtenerEstadoActual($id_pedido);
                
                if ($id_estado < $estado_actual) {
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Acción no permitida',
                        'text' => 'No puedes devolver un pedido a un estado anterior.'
                    ];
                } else {
                    if ($this->pedidoModel->cambiarEstado($id_pedido, $id_estado)) {
                        $_SESSION['alert'] = [
                            'icon' => 'success',
                            'title' => 'Estado Actualizado',
                            'text' => 'El pedido #' . str_pad($id_pedido, 5, '0', STR_PAD_LEFT) . ' ha sido actualizado exitosamente.'
                        ];
                    }
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

    public function eliminar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            header("Location: ../../views/admin/pedidos.php");
            exit;
        }

        $id_pedido = $_GET['id'] ?? null;

        if ($id_pedido) {
            $estado_actual = $this->pedidoModel->obtenerEstadoActual($id_pedido);
            
            // Solo permitir eliminar si está en estado 1 (Recibido)
            if ($estado_actual == 1) {
                if ($this->pedidoModel->eliminarPedido($id_pedido)) {
                    $_SESSION['alert'] = [
                        'icon' => 'success',
                        'title' => 'Pedido Eliminado',
                        'text' => 'El pedido fue eliminado permanentemente porque no se concretó el pago.'
                    ];
                } else {
                    $_SESSION['alert'] = [
                        'icon' => 'error',
                        'title' => 'Error',
                        'text' => 'Ocurrió un error al intentar eliminar el pedido.'
                    ];
                }
            } else {
                $_SESSION['alert'] = [
                    'icon' => 'error',
                    'title' => 'Acción Denegada',
                    'text' => 'Solo puedes eliminar pedidos que se encuentran en estado "Recibido".'
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
    case 'eliminar':
        $controller->eliminar();
        break;
    default:
        header("Location: ../../views/admin/pedidos.php");
        break;
}
?>
