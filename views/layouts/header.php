<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Tienda de Lujo y Cultura Urbana</title>
    <!-- Usa rutas absolutas para evitar problemas sin importar desde dónde se cargue -->
    <link rel="icon" href="/controlux/img/JC URBAN.png">
    <!-- Google Fonts: Montserrat -->
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;600;700;800;900&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Custom Header CSS -->
    <link href="/controlux/public/css/style_header.css?v=<?php echo time(); ?>" rel="stylesheet">
    <!-- Custom Cart CSS -->
    <link href="/controlux/public/css/style_carrito.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>

<!-- Navbar -->
<nav class="navbar navbar-expand-lg navbar-dark sticky-top bg-black" style="background-color: var(--black) !important;">
    <div class="container-fluid px-4">
        <!-- Si está logueado va al dashboard, si no, va al inicio público -->
        <a class="navbar-brand d-flex align-items-center" href="/controlux/public/index.php">
            <img src="/controlux/img/JC URBAN.png" alt="Logo JC URBAN" width="40" height="40" class="me-2" onerror="this.src='https://cdn-icons-png.flaticon.com/512/833/833400.png';">
            <span class="fw-bold">JC URBAN</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <?php
            $current_uri = $_SERVER['REQUEST_URI'];
            $is_categorias = strpos($current_uri, '/categorias/') !== false;
            $is_relojes = strpos($current_uri, '/categorias/relojes.php') !== false || strpos($current_uri, '/subcategorias/relojes/') !== false;
            $is_perfumes = strpos($current_uri, '/categorias/perfumes.php') !== false || strpos($current_uri, '/subcategorias/perfumes/') !== false;
            $is_accesorios = strpos($current_uri, '/categorias/accesorios.php') !== false || strpos($current_uri, '/subcategorias/accesorios/') !== false;
            $is_inicio = !$is_categorias;
            ?>
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo $is_inicio ? 'active' : ''; ?>" href="/controlux/public/index.php">Inicio</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle <?php echo $is_categorias ? 'active' : ''; ?>" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        Categorias
                    </a>
                    <ul class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <li><a class="dropdown-item <?php echo $is_relojes ? 'active' : ''; ?>" href="/controlux/views/categorias/relojes.php">Relojes</a></li>
                        <li><a class="dropdown-item <?php echo $is_perfumes ? 'active' : ''; ?>" href="/controlux/views/categorias/perfumes.php">Perfumes</a></li>
                        <li><a class="dropdown-item <?php echo $is_accesorios ? 'active' : ''; ?>" href="/controlux/views/categorias/accesorios.php">Accesorios</a></li>
                    </ul>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="https://wa.me/573212327275">Contacto</a>
                </li>
            </ul>
            <div class="d-flex align-items-center">
                <a href="#" class="cart-icon text-decoration-none me-3" data-bs-toggle="offcanvas" data-bs-target="#cartOffcanvas" aria-controls="cartOffcanvas" style="position: relative;">
                    <i class="bi bi-cart3" style="font-size: 1.5rem; color: var(--gold, #D4AF37);"></i>
                    <span id="cart-badge-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill" style="background-color: var(--gold, #D4AF37); color: var(--black, #0a0a0a);">0</span>
                </a>
                <?php if (isset($_SESSION['id_usuario'])): ?>
                    <div class="nav-item dropdown ms-2">
                        <a class="nav-link dropdown-toggle text-white d-flex align-items-center px-2 py-1 rounded" href="#" id="userDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false" style="border: 1px solid var(--gold, #D4AF37);">
                            <i class="bi bi-person-circle fs-5 me-2" style="color: var(--gold, #D4AF37);"></i>
                            <?php echo htmlspecialchars($_SESSION['usuario']['usuario'] ?? 'Mi Perfil'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                            <li><a class="dropdown-item" href="/controlux/views/cliente/perfil.php"><i class="bi bi-person-lines-fill me-2"></i>Información del usuario</a></li>
                            <?php if (isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == '1'): ?>
                                <li><a class="dropdown-item" href="/controlux/views/admin/dashboard_admin.php"><i class="bi bi-speedometer2 me-2"></i>Panel de Administración</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/controlux/controllers/Auth/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a href="/controlux/views/auth/login.php" class="btn btn-outline-gold btn-sm ms-2">Iniciar Sesión</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav><?php include __DIR__ . '/carrito.php'; ?>