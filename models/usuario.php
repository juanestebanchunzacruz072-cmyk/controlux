<?php
require_once __DIR__ . '/../config/database.php';

class Usuario
{
    private PDO $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function registrar(string $nombre, string $apellido, string $correo, string $password, ?string $telefono = null, ?string $direccion = null, ?string $ciudad = null, ?string $barrio = null, ?string $cedula = null, int $id_rol = 2, int $activo = 1)
    {
        try {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $query = 'INSERT INTO usuarios (id_rol, nombre, apellido, cedula, correo, password, telefono, direccion, ciudad, barrio, activo, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())';
            $stmt = $this->conn->prepare($query);
            return $stmt->execute([$id_rol, $nombre, $apellido, $cedula, $correo, $hashed_password, $telefono, $direccion, $ciudad, $barrio, $activo]);
        } catch (PDOException $e) {
            die("Error PDO al registrar: " . $e->getMessage());
        }
    }

    public function emailExiste(string $email)
    {
        $query = 'SELECT id_usuario FROM usuarios WHERE correo = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->rowCount() > 0;
    }

    public function obtenerPorEmail(string $email)
    {
        $query = 'SELECT id_usuario, nombre AS usuario, correo, password, id_rol, activo FROM usuarios WHERE correo = ?';
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function obtenerTodos()
    {
        $query = 'SELECT u.id_usuario, u.nombre AS usuario, u.correo AS email, u.id_rol, u.activo AS estado 
                  FROM usuarios u 
                  LEFT JOIN roles r ON u.id_rol = r.id_rol';
        $stmt = $this->conn->query($query);
        return $stmt->fetchAll();
    }

    public function obtenerEstados()
    {
        return [
            'Activo' => 'Activo',
            'Inactivo' => 'Inactivo'
        ];
    }

    public function actualizar(int $id_usuario, array $datos)
    {
        try {
            $query = 'UPDATE usuarios SET nombre = ?, id_rol = ?, activo = ?';
            $params = [$datos['usuario'], $datos['id_rol'], $datos['estado'] === 'Activo' ? 1 : 0];

            if (!empty($datos['email'])) {
                $query .= ', correo = ?';
                $params[] = $datos['email'];
            }

            $query .= ' WHERE id_usuario = ?';
            $params[] = $id_usuario;

            $stmt = $this->conn->prepare($query);
            $stmt->execute($params);
            return true;
        } catch (PDOException $e) {
            return 'Error en la base de datos: ' . $e->getMessage();
        }
    }

    public function eliminar(int $id_usuario)
    {
        try {
            $query = 'DELETE FROM usuarios WHERE id_usuario = ?';
            $stmt = $this->conn->prepare($query);
            $stmt->execute([$id_usuario]);
            return true;
        } catch (PDOException $e) {
            return 'Error en la base de datos al eliminar: ' . $e->getMessage();
        }
    }
}
?>
