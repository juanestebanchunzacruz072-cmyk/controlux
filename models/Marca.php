<?php
require_once __DIR__ . '/../config/database.php';

class Marca
{
    private PDO $conn;

    public function __construct()
    {
        global $conn;
        $this->conn = $conn;
    }

    public function insertar(string $nombre, string $descripcion, array $categorias = [], array $subcategorias = [])
    {
        try {
            $this->conn->beginTransaction();
            
            $stmt = $this->conn->prepare("INSERT INTO marcas (nombre, descripcion, activo) VALUES (?, ?, 1)");
            $stmt->execute([$nombre, $descripcion]);
            $id_marca = $this->conn->lastInsertId();
            
            if (!empty($categorias)) {
                $stmtCat = $this->conn->prepare("INSERT IGNORE INTO marcas_categorias (id_marca, id_categoria) VALUES (?, ?)");
                foreach ($categorias as $id_cat) {
                    $stmtCat->execute([$id_marca, $id_cat]);
                }
            }

            if (!empty($subcategorias)) {
                $stmtSub = $this->conn->prepare("INSERT IGNORE INTO marcas_subcategorias (id_marca, id_subcategoria) VALUES (?, ?)");
                foreach ($subcategorias as $id_sub) {
                    $stmtSub->execute([$id_marca, $id_sub]);
                }
            }
            
            $this->conn->commit();
            return $id_marca;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error insertando marca: " . $e->getMessage());
            return false;
        }
    }

    public function obtenerTodas()
    {
        try {
            // Obtener las marcas
            $stmt = $this->conn->query("SELECT id_marca, nombre, descripcion, activo FROM marcas ORDER BY id_marca DESC");
            $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener todas las relaciones para no hacer queries en loop
            $stmtCat = $this->conn->query("SELECT id_marca, id_categoria FROM marcas_categorias");
            $relCat = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
            
            $stmtSub = $this->conn->query("SELECT id_marca, id_subcategoria FROM marcas_subcategorias");
            $relSub = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

            // Agrupar relaciones por id_marca
            $catMap = [];
            foreach ($relCat as $rc) {
                $catMap[$rc['id_marca']][] = $rc['id_categoria'];
            }
            $subMap = [];
            foreach ($relSub as $rs) {
                $subMap[$rs['id_marca']][] = $rs['id_subcategoria'];
            }

            // Asignar relaciones a cada marca
            foreach ($marcas as &$marca) {
                $marca['categorias'] = $catMap[$marca['id_marca']] ?? [];
                $marca['subcategorias'] = $subMap[$marca['id_marca']] ?? [];
            }

            return $marcas;
        } catch (PDOException $e) {
            error_log("Error obteniendo marcas: " . $e->getMessage());
            return [];
        }
    }

    public function obtenerPaginadas($id_categoria = null, $limit = 10, $offset = 0, $filtro_busqueda = null)
    {
        try {
            $params = [];
            $where = "WHERE 1=1";
            
            if (!empty($id_categoria)) {
                $where .= " AND m.id_marca IN (SELECT id_marca FROM marcas_categorias WHERE id_categoria = ?)";
                $params[] = $id_categoria;
            }

            if (!empty($filtro_busqueda)) {
                $where .= " AND m.nombre LIKE ?";
                $params[] = "%" . trim($filtro_busqueda) . "%";
            }

            // Contar total
            $stmtCount = $this->conn->prepare("SELECT COUNT(DISTINCT m.id_marca) FROM marcas m $where");
            $stmtCount->execute($params);
            $total = $stmtCount->fetchColumn();

            // Obtener datos
            $sql = "SELECT DISTINCT m.id_marca, m.nombre, m.descripcion, m.activo FROM marcas m $where ORDER BY m.id_marca DESC LIMIT " . (int)$limit . " OFFSET " . (int)$offset;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute($params);
            $marcas = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Obtener relaciones
            if (!empty($marcas)) {
                $ids = array_column($marcas, 'id_marca');
                $placeholders = implode(',', array_fill(0, count($ids), '?'));
                
                $stmtCat = $this->conn->prepare("SELECT id_marca, id_categoria FROM marcas_categorias WHERE id_marca IN ($placeholders)");
                $stmtCat->execute($ids);
                $relCat = $stmtCat->fetchAll(PDO::FETCH_ASSOC);
                
                $stmtSub = $this->conn->prepare("SELECT id_marca, id_subcategoria FROM marcas_subcategorias WHERE id_marca IN ($placeholders)");
                $stmtSub->execute($ids);
                $relSub = $stmtSub->fetchAll(PDO::FETCH_ASSOC);

                $catMap = [];
                foreach ($relCat as $rc) {
                    $catMap[$rc['id_marca']][] = $rc['id_categoria'];
                }
                $subMap = [];
                foreach ($relSub as $rs) {
                    $subMap[$rs['id_marca']][] = $rs['id_subcategoria'];
                }

                foreach ($marcas as &$marca) {
                    $marca['categorias'] = $catMap[$marca['id_marca']] ?? [];
                    $marca['subcategorias'] = $subMap[$marca['id_marca']] ?? [];
                }
            }

            return ['marcas' => $marcas, 'total' => $total];
        } catch (PDOException $e) {
            error_log("Error obteniendo marcas paginadas: " . $e->getMessage());
            return ['marcas' => [], 'total' => 0];
        }
    }

    public function actualizar(int $id_marca, string $nombre, string $descripcion, array $categorias = [], array $subcategorias = [])
    {
        try {
            $this->conn->beginTransaction();
            
            // Actualizar tabla principal
            $stmt = $this->conn->prepare("UPDATE marcas SET nombre = ?, descripcion = ? WHERE id_marca = ?");
            $stmt->execute([$nombre, $descripcion, $id_marca]);
            
            // Limpiar relaciones antiguas
            $this->conn->prepare("DELETE FROM marcas_categorias WHERE id_marca = ?")->execute([$id_marca]);
            $this->conn->prepare("DELETE FROM marcas_subcategorias WHERE id_marca = ?")->execute([$id_marca]);

            // Insertar nuevas categorías
            if (!empty($categorias)) {
                $stmtCat = $this->conn->prepare("INSERT IGNORE INTO marcas_categorias (id_marca, id_categoria) VALUES (?, ?)");
                foreach ($categorias as $id_cat) {
                    $stmtCat->execute([$id_marca, $id_cat]);
                }
            }

            // Insertar nuevas subcategorías
            if (!empty($subcategorias)) {
                $stmtSub = $this->conn->prepare("INSERT IGNORE INTO marcas_subcategorias (id_marca, id_subcategoria) VALUES (?, ?)");
                foreach ($subcategorias as $id_sub) {
                    $stmtSub->execute([$id_marca, $id_sub]);
                }
            }
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error actualizando marca: " . $e->getMessage());
            return false;
        }
    }

    public function cambiarEstado(int $id_marca, int $estado)
    {
        try {
            $stmt = $this->conn->prepare("UPDATE marcas SET activo = ? WHERE id_marca = ?");
            $stmt->execute([$estado, $id_marca]);
            return true;
        } catch (PDOException $e) {
            error_log("Error cambiando estado de marca: " . $e->getMessage());
            return false;
        }
    }

    public function eliminar(int $id_marca)
    {
        try {
            $this->conn->beginTransaction();
            
            // Eliminar relaciones primero
            $this->conn->prepare("DELETE FROM marcas_categorias WHERE id_marca = ?")->execute([$id_marca]);
            $this->conn->prepare("DELETE FROM marcas_subcategorias WHERE id_marca = ?")->execute([$id_marca]);
            
            // Eliminar marca
            $stmt = $this->conn->prepare("DELETE FROM marcas WHERE id_marca = ?");
            $stmt->execute([$id_marca]);
            
            $this->conn->commit();
            return true;
        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Error eliminando marca: " . $e->getMessage());
            return false;
        }
    }
}
?>
