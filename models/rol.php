<?php
require_once __DIR__ . '/../config/database.php';

class Rol
{
    private PDO $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function obtenerTodos()
    {
        $query = 'SELECT id_rol, nombre AS rol FROM roles';
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll();
    }
}
?>