<?php
session_start();

if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['carrito_temporal'])) {
    header("Location: ../../public/index.php");
    exit;
}

require_once __DIR__ . '/../../models/Pedido.php';
require_once __DIR__ . '/../../models/Producto.php';

class PedidoClienteController
{
    private Pedido $pedidoModel;
    private Producto $productoModel;

    public function __construct()
    {
        $this->pedidoModel = new Pedido();
        $this->productoModel = new Producto();
    }

    public function guardar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header("Location: ../../views/cliente/detalle_pedido.php");
            exit;
        }

        try {
            $id_usuario = $_SESSION['id_usuario'];
            $carrito = $_SESSION['carrito_temporal'];
            $total = isset($_POST['total']) ? (float)$_POST['total'] : 0;
            
            $this->pedidoModel->getConexion()->beginTransaction();
            
            // 1. Insertar el Pedido
            $id_pedido = $this->pedidoModel->insertarPedido($id_usuario, $total);
            
            // Obtener datos del cliente para el mensaje
            $stmt_cliente = $this->pedidoModel->getConexion()->prepare("SELECT nombre, apellido, direccion, ciudad, telefono, cedula, barrio FROM usuarios WHERE id_usuario = ?");
            $stmt_cliente->execute([$id_usuario]);
            $cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);
            
            $nombre_cliente = $cliente['nombre'] . ' ' . $cliente['apellido'];
            $direccion_envio = $cliente['direccion'];
            $ciudad_envio = $cliente['ciudad'];
            $telefono_cliente = $cliente['telefono'];
            $cedula_cliente = $cliente['cedula'];
            $barrio_cliente = $cliente['barrio'];
            
            $mensaje = "Hola JC URBAN\n";
            $mensaje .= "Quiero confirmar mi pedido (ID: #$id_pedido).\n";
            $mensaje .= "Datos del cliente y de envio:\n";
            $mensaje .= "- Nombre: $nombre_cliente\n";
            if (!empty($cedula_cliente)) {
                $mensaje .= "- Cédula: $cedula_cliente\n";
            }
            $mensaje .= "- Dirección: $direccion_envio\n";
            if (!empty($barrio_cliente)) {
                $mensaje .= "- Barrio: $barrio_cliente\n";
            }
            $mensaje .= "- Ciudad: $ciudad_envio\n";
            if (!empty($telefono_cliente)) {
                $mensaje .= "- Teléfono: $telefono_cliente\n";
            }
            $mensaje .= "Resumen de mi compra:\n";
            
            foreach ($carrito as $item) {
                $id_producto = isset($item['id']) ? $item['id'] : 0;
                $nombre = $item['name'];
                $cantidad = $item['quantity'];
                $precio = $item['price'];
                $subtotal_item = $precio * $cantidad;
                
                // 2. Insertar Detalle_Pedido
                if ($id_producto > 0) {
                    $this->pedidoModel->insertarDetalle($id_pedido, $id_producto, $cantidad, $precio, $subtotal_item);
                    
                    // Descontar el stock
                    $this->productoModel->descontarStock($id_producto, $cantidad);
                }
                
                $mensaje .= "  * {$cantidad}x $nombre ($" . number_format($precio, 0, ',', '.') . " c/u)\n";
            }
            
            $mensaje .= "\nTotal a Pagar: $" . number_format($total, 0, ',', '.');
            $resumen_texto = urlencode($mensaje);
            
            $this->pedidoModel->getConexion()->commit();
            
            // Guardar el ID del pedido para la factura
            $_SESSION['ultimo_pedido'] = $id_pedido;
            
            // Limpiar el carrito de la sesión
            unset($_SESSION['carrito_temporal']);
            
            // Redirigir a WhatsApp
            $numero_wa = "+573212327275"; // REEMPLAZAR POR EL NUMERO REAL
            header("Location: https://wa.me/$numero_wa?text=$resumen_texto");
            exit;
            
        } catch (PDOException $e) {
            if ($this->pedidoModel->getConexion()->inTransaction()) {
                $this->pedidoModel->getConexion()->rollBack();
            }
            die("Error al procesar el pedido: " . $e->getMessage());
        }
    }
}

$controller = new PedidoClienteController();
$accion = $_GET['accion'] ?? '';

switch ($accion) {
    case 'guardar':
        $controller->guardar();
        break;
    default:
        header("Location: ../../views/cliente/detalle_pedido.php");
        break;
}
?>
