<?php
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!-- Mobile Menu Toggle Button -->
<button id="mobile-menu-toggle" class="d-md-none" style="position: fixed; top: 15px; left: 15px; z-index: 1001; background: var(--black, #0a0a0a); color: var(--gold, #D4AF37); border: 1px solid var(--gold, #D4AF37); border-radius: 4px; padding: 5px 10px; font-size: 1.5rem; cursor: pointer; display: none;">
    <i class="bi bi-list"></i>
</button>

<!-- Sidebar Overlay para móviles -->
<div id="sidebar-overlay" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999;"></div>

<!-- Sidebar -->
<aside class="sidebar" id="admin-sidebar">
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const mobileBtn = document.getElementById('mobile-menu-toggle');
    const sidebar = document.getElementById('admin-sidebar');
    const overlay = document.getElementById('sidebar-overlay');

    if (mobileBtn && sidebar && overlay) {
        function toggleMenu() {
            sidebar.classList.toggle('show');
            if (sidebar.classList.contains('show')) {
                overlay.style.display = 'block';
                document.body.style.overflow = 'hidden';
            } else {
                overlay.style.display = 'none';
                document.body.style.overflow = '';
            }
        }

        mobileBtn.addEventListener('click', toggleMenu);
        overlay.addEventListener('click', toggleMenu);
    }
});
</script>
