<?php
require_once __DIR__ . '/../config/database.php';

class Subcategoria
{
    private PDO $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function obtenerPorCategoria(int $id_categoria)
    {
        $stmt = $this->conn->prepare("SELECT id_subcategoria, nombre FROM subcategoria WHERE id_categoria = :id_categoria ORDER BY nombre ASC");
        $stmt->bindParam(':id_categoria', $id_categoria, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
