<?php
require_once __DIR__ . '/../config/database.php';

class Pedido
{
    private PDO $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function insertarPedido(int $id_usuario, float $total)
    {
        $stmt = $this->conn->prepare("INSERT INTO pedidos (id_usuario, fecha_pedido, subtotal, total, id_estado) VALUES (?, NOW(), ?, ?, 1)");
        $stmt->execute([$id_usuario, $total, $total]);
        return $this->conn->lastInsertId();
    }

    public function insertarDetalle(int $id_pedido, int $id_producto, int $cantidad, float $precio, float $subtotal)
    {
        $stmt = $this->conn->prepare("INSERT INTO detalle_pedidos (id_pedido, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        return $stmt->execute([$id_pedido, $id_producto, $cantidad, $precio, $subtotal]);
    }

    public function cambiarEstado(int $id_pedido, int $id_estado)
    {
        $stmt = $this->conn->prepare("UPDATE pedidos SET id_estado = :id_estado WHERE id_pedido = :id_pedido");
        $stmt->bindParam(':id_estado', $id_estado, PDO::PARAM_INT);
        $stmt->bindParam(':id_pedido', $id_pedido, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getConexion()
    {
        return $this->conn;
    }
}
?>
