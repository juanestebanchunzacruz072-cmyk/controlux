<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/database.php';

// Fetch orders with user info and state
$pedidos = [];
try {
    $stmt = $conn->query("
        SELECT p.id_pedido, p.fecha_pedido, p.total, p.direccion_entrega, 
               u.nombre, u.apellido, e.nombre as estado, e.id_estado
        FROM pedidos p 
        INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
        INNER JOIN estado_pedidos e ON p.id_estado = e.id_estado
        ORDER BY p.id_pedido DESC
    ");
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Si la tabla pedidos no tiene registros o hay un error
    $error = "No se pudieron cargar los pedidos. " . $e->getMessage();
}

// Obtener la lista de estados para el select
$estados = [];
try {
    $stmt = $conn->query("SELECT * FROM estado_pedidos ORDER BY id_estado ASC");
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Gestión de Pedidos</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style_dashboard_admin.css?v=<?php echo time(); ?>">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../public/css/style_productos_admin.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Sidebar -->
    <aside class="sidebar">
        <div class="sidebar-brand">
            <h2>JC URBAN</h2>
            <p>PANEL DE ADMINISTRACIÓN</p>
        </div>
        
        <div class="sidebar-menu">
            <div class="menu-category">PRINCIPAL</div>
            <a href="dashboard_admin.php" class="menu-item">
                <i class="bi bi-grid-1x2-fill"></i> DASHBOARD
            </a>
            
            <div class="menu-category">CATÁLOGO</div>
            <a href="productos.php" class="menu-item">
                <i class="bi bi-box-seam"></i> PRODUCTOS
            </a>
            <a href="agregar_marca.php" class="menu-item">
                <i class="bi bi-tags"></i> AGREGAR MARCA
            </a>
            
            <div class="menu-category">GESTIÓN</div>
            <a href="usuarios.php" class="menu-item">
                <i class="bi bi-people"></i> USUARIOS
            </a>
            <a href="pedidos.php" class="menu-item active">
                <i class="bi bi-bag-check"></i> PEDIDOS
            </a>
        </div>
        
        <div class="sidebar-footer">
            <div class="admin-profile">
                <div class="admin-avatar">A</div>
                <div class="admin-info">
                    <h6>Administrador</h6>
                    <p><?php echo htmlspecialchars($_SESSION['usuario']['correo'] ?? 'admin@jcurban.com'); ?></p>
                </div>
            </div>
            <a href="../../controllers/Auth/logout.php" class="logout-btn" title="Cerrar sesión">
                <i class="bi bi-box-arrow-right"></i>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar">
            <h1>GESTIÓN DE PEDIDOS</h1>
            <div class="topbar-actions">
                <a href="dashboard_admin.php" class="btn-outline-dark-custom">
                    <i class="bi bi-arrow-left"></i> VOLVER
                </a>
            </div>
        </header>

        <section class="table-container-section mt-4">
            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title">HISTORIAL DE PEDIDOS</h3>
            </div>
            
            <?php if (isset($error)): ?>
                <div style="background: #f8d7da; color: #842029; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID PEDIDO</th>
                            <th>CLIENTE</th>
                            <th>FECHA</th>
                            <th>DIRECCIÓN ENTREGA</th>
                            <th>TOTAL</th>
                            <th>ESTADO ACTUAL</th>
                            <th>CAMBIAR ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Aún no se han registrado pedidos en la tienda.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pedidos as $p): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($p['id_pedido'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido']); ?></td>
                                <td><?php echo date('d/m/Y H:i', strtotime($p['fecha_pedido'])); ?></td>
                                <td><?php echo htmlspecialchars($p['direccion_entrega'] ?? 'Pendiente'); ?></td>
                                <td class="price-col">$ <?php echo number_format($p['total'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="estado-badge estado-<?php echo $p['id_estado']; ?>">
                                        <?php echo htmlspecialchars($p['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <form action="../../controllers/Admin/PedidoController.php?accion=cambiarEstado" method="POST" class="d-flex align-items-center">
                                        <input type="hidden" name="id_pedido" value="<?php echo $p['id_pedido']; ?>">
                                        <select name="id_estado" class="select-estado">
                                            <?php foreach ($estados as $est): ?>
                                                <option value="<?php echo $est['id_estado']; ?>" <?php echo ($est['id_estado'] == $p['id_estado']) ? 'selected' : ''; ?>>
                                                    <?php echo htmlspecialchars($est['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <button type="submit" class="btn-update"><i class="bi bi-check2"></i></button>
                                    </form>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <?php
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo "<script>
            Swal.fire({
                icon: '{$alert['icon']}',
                title: '{$alert['title']}',
                text: '{$alert['text']}',
                confirmButtonColor: '#D4AF37',
                background: '#111',
                color: '#fff'
            });
        </script>";
        unset($_SESSION['alert']);
    }
    ?>
</body>
</html>
