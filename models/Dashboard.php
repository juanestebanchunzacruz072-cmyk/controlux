<?php
require_once __DIR__ . '/../config/database.php';

class Dashboard
{
    private $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function getTotalProductos()
    {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM productos");
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getTotalUsuarios()
    {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM usuarios WHERE id_rol = '2'");
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getTotalPedidos()
    {
        try {
            $stmt = $this->conn->query("SELECT COUNT(*) as total FROM pedidos");
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (PDOException $e) {
            return 7; // Valor fallback original si la tabla no existe
        }
    }

    public function getValorCatalogo()
    {
        try {
            $stmt = $this->conn->query("SELECT SUM(precio * stock) as total FROM productos");
            return $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        } catch (PDOException $e) {
            return 0;
        }
    }

    public function getProductosRecientes($limit = 4)
    {
        try {
            $stmt = $this->conn->query("
                SELECT p.id_producto, p.nombre, p.precio, p.activo, p.genero, c.nombre as categoria, s.nombre as subcategoria, p.img 
                FROM productos p 
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
                LEFT JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                ORDER BY p.id_producto DESC LIMIT $limit
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            try {
                $stmt = $this->conn->query("SELECT * FROM productos ORDER BY id_producto DESC LIMIT $limit");
                return $stmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e2) {
                return [];
            }
        }
    }

    public function getProductosMasVendidos($limit = 4)
    {
        try {
            $stmt = $this->conn->query("
                SELECT p.id_producto, p.nombre, p.precio, p.activo, p.genero, c.nombre as categoria, s.nombre as subcategoria, p.img, SUM(dp.cantidad) as total_vendido
                FROM productos p
                JOIN detalle_pedidos dp ON p.id_producto = dp.id_producto
                LEFT JOIN categorias c ON p.id_categoria = c.id_categoria
                LEFT JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
                GROUP BY p.id_producto, p.nombre, p.precio, p.activo, p.genero, c.nombre, s.nombre, p.img
                ORDER BY total_vendido DESC
                LIMIT $limit
            ");
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }
}
?>
