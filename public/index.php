<?php 
session_start();
// Se permite que tanto admins como clientes vean el index público.

include '../views/layouts/header.php'; 
?>
<section class="hero-section text-center text-white d-flex align-items-center justify-content-center position-relative overflow-hidden" style="min-height: 90vh; margin-top: -24px;">
    <!-- Video Background -->
    <video autoplay muted loop playsinline class="position-absolute w-100 h-100" style="object-fit: cover; z-index: -2; top: 0; left: 0;">
        <source src="../img/videos/relojes.mp4" type="video/mp4">
    </video>
    
    <!-- Dark overlay for text readability -->
    <div class="position-absolute w-100 h-100 bg-dark" style="opacity: 0.4; z-index: -1; top: 0; left: 0;"></div>

    <div class="container py-5 position-relative" style="z-index: 1;">
        <h1 class="display-3 fw-bold mb-4" style="color: var(--white-pure);">Estilo sin <span style="color: var(--gold-premium);">Límites</span></h1>
        <p class="lead mb-4" style="color: var(--white-pure);">Descubre nuestro catalogo de puro lujo y exclusividad</p>
        <a href="#categorias" class="btn btn-gold btn-lg px-5 py-3 rounded-pill">Explorar Catalogo</a>
    </div>
</section>

<!-- Categories Section -->
<section id="categorias" class="categories-section container my-5">
    <h2 class="text-center">Categorías</h2>

    <div class="categories-grid">
        <a href="../views/categorias/relojes.php" class="category-card">
            <div class="category-img-container">
                <img src="../img/reloj.webp" alt="Relojes" class="category-img">
            </div>
            <h3 class="category-title">Relojes</h3>
        </a>
        <a href="../views/categorias/perfumes.php" class="category-card">
            <div class="category-img-container">
                <img src="../img/perfume.webp" alt="Perfumes" class="category-img">
            </div>
            <h3 class="category-title">Perfumes</h3>
        </a>
        <a href="../views/categorias/accesorios.php" class="category-card">
            <div class="category-img-container">
                <img src="../img/accesorio.png" alt="Accesorios" class="category-img">
            </div>
            <h3 class="category-title">Accesorios</h3>
        </a>
    </div>
</section>

<link href="/controlux/public/css/style.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('welcome')) {
            const nombreUsuario = <?php echo json_encode($_SESSION['usuario']['usuario'] ?? 'Cliente'); ?>;
            Swal.fire({
                title: '¡Bienvenido ' + nombreUsuario + '!',
                text: 'Has iniciado sesión correctamente.',
                icon: 'success',
                confirmButtonColor: '#D4AF37',
                confirmButtonText: 'Aceptar',
                background: '#0a0a0a',
                color: '#fff'
            });
            // Limpiar la URL
            window.history.replaceState(null, null, window.location.pathname);
        }

        if (urlParams.has('pedido_exito')) {
            const idUltimoPedido = <?php echo json_encode($_SESSION['ultimo_pedido'] ?? null); ?>;
            Swal.fire({
                title: '¡Pedido Realizado!',
                text: 'Tu pedido se ha procesado correctamente y la confirmación se ha enviado a WhatsApp.',
                icon: 'success',
                confirmButtonColor: '#D4AF37',
                confirmButtonText: 'Ver Factura',
                background: '#0a0a0a',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed && idUltimoPedido) {
                    window.location.href = '../views/cliente/factura.php?id=' + idUltimoPedido;
                }
            });
            // Limpiar la URL para que no vuelva a salir al recargar
            window.history.replaceState(null, null, window.location.pathname);
        }
    });
</script>

<?php include '../views/layouts/footer.php'; ?>