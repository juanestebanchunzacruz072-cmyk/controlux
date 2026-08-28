<?php
// Si se accede a esta vista directamente, invocamos el controlador para no romper enlaces existentes.
if (!isset($total_productos)) {
    require_once '../../controllers/Admin/DashboardController.php';
    $controller = new DashboardController();
    $controller->index();
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Panel de Administración</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style_dashboard_admin.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Sidebar -->
    <?php include '../layouts/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Topbar -->
        <header class="topbar">
            <h1>DASHBOARD</h1>
            <div class="topbar-actions">
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    En línea
                </div>
                <a href="../../public/index.php" class="btn-outline-dark-custom" target="_blank" title="Abrir tienda en una nueva pestaña">
                    <i class="bi bi-shop"></i> VER TIENDA
                </a>
            </div>
        </header>

        <!-- Welcome Section -->
        <section class="welcome-section">
            <h2>BIENVENIDO DE NUEVO</h2>
            <p>Resumen general de JC URBAN</p>
        </section>

        <!-- Stats Grid -->
        <section class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon gold">
                    <i class="bi bi-box"></i>
                </div>
                <h3 class="stat-value"><?php echo $total_productos; ?></h3>
                <span class="stat-label">PRODUCTOS</span>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="bi bi-people"></i>
                </div>
                <h3 class="stat-value"><?php echo $total_usuarios; ?></h3>
                <span class="stat-label">USUARIOS</span>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="bi bi-bag"></i>
                </div>
                <h3 class="stat-value"><?php echo is_numeric($total_pedidos) ? $total_pedidos : '0'; ?></h3>
                <span class="stat-label">PEDIDOS</span>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="bi bi-graph-up-arrow"></i>
                </div>
                <h3 class="stat-value">$<?php echo number_format($valor_catalogo, 0, ',', '.'); ?></h3>
                <span class="stat-label">VALOR CATÁLOGO</span>
            </div>
        </section>

        <!-- Table Section -->
        <section class="table-container-section">
            <div class="section-header">
                <h3 class="section-title">PRODUCTOS RECIENTES</h3>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>IMAGEN</th>
                            <th>NOMBRE</th>
                            <th>CATEGORÍA</th>
                            <th>PRECIO</th>
                            <th>ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos_recientes)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">No hay productos registrados aún.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos_recientes as $p): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($p['img'])): ?>
                                        <img src="../../<?php echo htmlspecialchars($p['img']); ?>" class="table-img">
                                    <?php else: ?>
                                        <div class="table-img d-flex align-items-center justify-content-center bg-light">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($p['nombre'] ?? 'Producto'); ?></td>
                                <td>
                                    <?php 
                                    $cat_text = htmlspecialchars($p['categoria'] ?? 'Sin categoría');
                                    if (!empty($p['subcategoria'])) {
                                        $cat_text .= ' &gt; ' . htmlspecialchars($p['subcategoria']);
                                    }
                                    if (!empty($p['genero']) && $p['genero'] !== 'Unisex') {
                                        $cat_text .= ' (' . htmlspecialchars($p['genero']) . ')';
                                    }
                                    echo $cat_text;
                                    ?>
                                </td>
                                <td class="price-col">$ <?php echo number_format($p['precio'] ?? 0, 0, ',', '.'); ?></td>
                                <td>
                                    <?php if (isset($p['activo']) && $p['activo'] == 1): ?>
                                        <span class="badge-status disponible">ACTIVO</span>
                                    <?php else: ?>
                                        <span class="badge-status" style="background-color: #e2e3e5; color: #383d41;">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Productos Más Vendidos -->
        <section class="table-container-section mt-5" style="margin-top: 2rem;">
            <div class="section-header">
                <h3 class="section-title">PRODUCTOS MÁS VENDIDOS</h3>
            </div>
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>IMAGEN</th>
                            <th>NOMBRE</th>
                            <th>CATEGORÍA</th>
                            <th>UNIDADES VENDIDAS</th>
                            <th>PRECIO</th>
                            <th>ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos_mas_vendidos)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">Aún no se han registrado ventas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos_mas_vendidos as $p): ?>
                            <tr>
                                <td>
                                    <?php if (!empty($p['img'])): ?>
                                        <img src="../../<?php echo htmlspecialchars($p['img']); ?>" class="table-img">
                                    <?php else: ?>
                                        <div class="table-img d-flex align-items-center justify-content-center bg-light">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($p['nombre'] ?? 'Producto'); ?></td>
                                <td>
                                    <?php 
                                    $cat_text = htmlspecialchars($p['categoria'] ?? 'Sin categoría');
                                    if (!empty($p['subcategoria'])) {
                                        $cat_text .= ' &gt; ' . htmlspecialchars($p['subcategoria']);
                                    }
                                    if (!empty($p['genero']) && $p['genero'] !== 'Unisex') {
                                        $cat_text .= ' (' . htmlspecialchars($p['genero']) . ')';
                                    }
                                    echo $cat_text;
                                    ?>
                                </td>
                                <td class="fw-bold" style="color: var(--gold, #D4AF37); font-size: 1.1rem;"><?php echo htmlspecialchars($p['total_vendido']); ?> uds.</td>
                                <td class="price-col">$ <?php echo number_format($p['precio'] ?? 0, 0, ',', '.'); ?></td>
                                <td>
                                    <?php if (isset($p['activo']) && $p['activo'] == 1): ?>
                                        <span class="badge-status disponible">ACTIVO</span>
                                    <?php else: ?>
                                        <span class="badge-status" style="background-color: #e2e3e5; color: #383d41;">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('welcome')) {
                Swal.fire({
                    title: '¡Bienvenido Administrador!',
                    text: 'Has iniciado sesión correctamente en el panel.',
                    icon: 'success',
                    confirmButtonColor: '#D4AF37',
                    confirmButtonText: 'Aceptar'
                });
                // Limpiar la URL
                window.history.replaceState(null, null, window.location.pathname);
            }
        });
    </script>
</body>
</html>
