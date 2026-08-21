<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/database.php';

// Fetch users (role 2)
$usuarios = [];
try {
    $stmt = $conn->query("
        SELECT id_usuario, nombre, apellido, correo, telefono, direccion, ciudad, fecha_registro
        FROM usuarios 
        WHERE id_rol = '2'
        ORDER BY fecha_registro DESC
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar los usuarios: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Gestión de Usuarios</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style_dashboard_admin.css?v=<?php echo time(); ?>">
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
            <a href="usuarios.php" class="menu-item active">
                <i class="bi bi-people"></i> USUARIOS
            </a>
            <a href="pedidos.php" class="menu-item">
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
            <h1>LISTADO DE CLIENTES</h1>
            <div class="topbar-actions">
                <a href="dashboard_admin.php" class="btn-outline-dark-custom">
                    <i class="bi bi-arrow-left"></i> VOLVER
                </a>
            </div>
        </header>

        <section class="table-container-section mt-4">
            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title">CLIENTES REGISTRADOS</h3>
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
                            <th>PERFIL</th>
                            <th>NOMBRE COMPLETO</th>
                            <th>CORREO ELÉCTRONICO</th>
                            <th>TELÉFONO</th>
                            <th>UBICACIÓN</th>
                            <th>FECHA REGISTRO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay clientes registrados aún.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1)); ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($u['correo']); ?></td>
                                <td><?php echo htmlspecialchars($u['telefono'] ?? 'No especificado'); ?></td>
                                <td>
                                    <?php 
                                        $ubicacion = array_filter([$u['ciudad'], $u['direccion']]);
                                        echo !empty($ubicacion) ? htmlspecialchars(implode(", ", $ubicacion)) : 'No especificada';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge-status disponible" style="background-color: #333; color: white;">
                                        <?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

</body>
</html>
