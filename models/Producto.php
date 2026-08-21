<?php
require_once __DIR__ . '/../config/database.php';

class Producto
{
    private PDO $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function insertar(array $datos, string $fecha_creacion)
    {
        $sql = "INSERT INTO productos (id_marca, id_categoria, id_subcategoria, genero, referencia, nombre, descripcion, precio, stock, activo, fecha_creacion) 
                VALUES (:id_marca, :id_categoria, :id_subcategoria, :genero, :referencia, :nombre, :descripcion, :precio, :stock, 1, :fecha_creacion)";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_marca', $datos['id_marca']);
        $stmt->bindParam(':id_categoria', $datos['id_categoria']);
        $stmt->bindParam(':id_subcategoria', $datos['id_subcategoria']);
        $stmt->bindParam(':genero', $datos['genero']);
        $stmt->bindParam(':referencia', $datos['referencia']);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':precio', $datos['precio']);
        $stmt->bindParam(':stock', $datos['stock']);
        $stmt->bindParam(':fecha_creacion', $fecha_creacion);
        
        $stmt->execute();
        return $this->conn->lastInsertId();
    }

    public function obtenerRecomendaciones($limite = 4)
    {
        $query = "SELECT p.id_producto, p.nombre, p.precio, p.stock, p.img 
                  FROM productos p
                  WHERE p.activo = 1 AND p.stock > 0 
                  ORDER BY RAND() LIMIT :limite";
        $stmt = $this->conn->prepare($query);
        $stmt->bindValue(':limite', (int)$limite, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function actualizar(int $id_producto, array $datos)
    {
        $sql = "UPDATE productos SET 
                nombre = :nombre,
                referencia = :referencia,
                id_categoria = :id_categoria,
                id_subcategoria = :id_subcategoria,
                id_marca = :id_marca,
                genero = :genero,
                precio = :precio,
                stock = :stock,
                descripcion = :descripcion,
                fecha_actualizacion = CURRENT_TIMESTAMP
                WHERE id_producto = :id_producto";
                
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':nombre', $datos['nombre']);
        $stmt->bindParam(':referencia', $datos['referencia']);
        $stmt->bindParam(':id_categoria', $datos['id_categoria']);
        $stmt->bindParam(':id_subcategoria', $datos['id_subcategoria']);
        $stmt->bindParam(':id_marca', $datos['id_marca']);
        $stmt->bindParam(':genero', $datos['genero']);
        $stmt->bindParam(':precio', $datos['precio']);
        $stmt->bindParam(':stock', $datos['stock']);
        $stmt->bindParam(':descripcion', $datos['descripcion']);
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    public function toggleEstado(int $id_producto, int $nuevo_estado)
    {
        $stmt = $this->conn->prepare("UPDATE productos SET activo = :nuevo_estado WHERE id_producto = :id_producto");
        $stmt->bindParam(':nuevo_estado', $nuevo_estado, PDO::PARAM_INT);
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function descontarStock(int $id_producto, int $cantidad)
    {
        $stmt = $this->conn->prepare("UPDATE productos SET stock = stock - :cantidad WHERE id_producto = :id_producto AND stock >= :cantidad");
        $stmt->bindParam(':cantidad', $cantidad, PDO::PARAM_INT);
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function insertarImagen(int $id_producto, string $ruta_imagen, string $nombre_imagen, string $fecha_creacion)
    {
        $sql = "UPDATE productos SET img = :img WHERE id_producto = :id_producto";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt->bindParam(':img', $ruta_imagen);
        return $stmt->execute();
    }

    public function actualizarImagen(int $id_producto, string $ruta_imagen)
    {
        $sql = "UPDATE productos SET img = :img WHERE id_producto = :id_producto";
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':id_producto', $id_producto, PDO::PARAM_INT);
        $stmt->bindParam(':img', $ruta_imagen);
        return $stmt->execute();
    }

    public function obtenerCatalogo(string $categoria, string $palabraClave)
    {
        $sql = "
            SELECT p.id_producto, p.nombre, p.precio, p.genero, p.stock, p.descripcion, m.nombre as marca, p.img 
            FROM productos p
            INNER JOIN categorias c ON p.id_categoria = c.id_categoria
            LEFT JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
            LEFT JOIN marcas m ON p.id_marca = m.id_marca
            WHERE c.nombre = :categoria 
              AND (m.nombre LIKE :palabraClave OR s.nombre LIKE :palabraClave OR p.nombre LIKE :palabraClave)
              AND p.activo = 1
            ORDER BY p.id_producto DESC
        ";
        
        $stmt = $this->conn->prepare($sql);
        $stmt->bindParam(':categoria', $categoria);
        $likeParam = '%' . $palabraClave . '%';
        $stmt->bindParam(':palabraClave', $likeParam);
        
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function eliminar(int $id_producto)
    {
        try {
            $this->conn->beginTransaction();
            
            // Eliminar producto
            $stmt = $this->conn->prepare("DELETE FROM productos WHERE id_producto = ?");
            $stmt->execute([$id_producto]);
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error eliminando producto: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerPorId(int $id_producto)
    {
        $stmt = $this->conn->prepare("SELECT * FROM productos WHERE id_producto = :id");
        $stmt->bindParam(':id', $id_producto, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function obtenerCategorias()
    {
        $stmt = $this->conn->query("SELECT id_categoria, nombre FROM categorias ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMarcas()
    {
        $stmt = $this->conn->query("SELECT id_marca, nombre FROM marcas ORDER BY nombre ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerMarcasCategorias()
    {
        $stmt = $this->conn->query("SELECT DISTINCT id_marca, id_categoria FROM productos WHERE id_marca IS NOT NULL");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConexion() {
        return $this->conn;
    }
}

