<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Sidebar -->
<aside class="sidebar">
    <div class="sidebar-brand">
        <h2>JC URBAN</h2>
        <p>PANEL DE ADMINISTRACIÓN</p>
    </div>
    
    <div class="sidebar-menu">
        <div class="menu-category">PRINCIPAL</div>
        <a href="dashboard_admin.php" class="menu-item <?php echo ($current_page == 'dashboard_admin.php') ? 'active' : ''; ?>">
            <i class="bi bi-grid-1x2-fill"></i> DASHBOARD
        </a>
        
        <div class="menu-category">CATÁLOGO</div>
        <a href="productos.php" class="menu-item <?php echo ($current_page == 'productos.php') ? 'active' : ''; ?>">
            <i class="bi bi-box-seam"></i> PRODUCTOS
        </a>
        <a href="agregar_marca.php" class="menu-item <?php echo ($current_page == 'agregar_marca.php') ? 'active' : ''; ?>">
            <i class="bi bi-tags"></i> MARCAS
        </a>
        
        <div class="menu-category">GESTIÓN</div>
        <a href="usuarios.php" class="menu-item <?php echo ($current_page == 'usuarios.php') ? 'active' : ''; ?>">
            <i class="bi bi-people"></i> USUARIOS
        </a>
        <a href="pedidos.php" class="menu-item <?php echo ($current_page == 'pedidos.php') ? 'active' : ''; ?>">
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
