<?php include '../../views/layouts/header.php'; ?>

<!-- Categories Section -->
<section id="categorias" class="categories-section container my-5 position-relative">
    <a href="../../public/index.php#categorias" class="btn btn-outline-dark position-absolute" style="top: 0; left: 15px; font-weight: 600; font-size: 1.1rem; padding: 10px 20px;">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
    
    <h2 class="text-center mb-4">Perfumes</h2>

    <div class="categories-grid" style="display: flex; justify-content: center; gap: 2rem;">
        <a href="subcategorias/perfumes/perfumes_hombres.php" class="category-card" style="flex: 0 1 calc(33.333% - 2rem); max-width: 400px;">
            <div class="category-img-container">
                <img src="../../img/perfumes/Khamrah.webp" alt="Hombre" class="category-img">
            </div>
            <h3 class="category-title">Hombre</h3>
        </a>
        
        <a href="subcategorias/perfumes/perfumes_mujer.php" class="category-card" style="flex: 0 1 calc(33.333% - 2rem); max-width: 400px;">
            <div class="category-img-container">
                <img src="../../img/perfumes/YaraElixir.webp" alt="Mujer" class="category-img">
            </div>
            <h3 class="category-title">Mujer</h3>
        </a>
    </div>
</section>

<link href="/controlux/public/css/style.css" rel="stylesheet">

<?php include '../../views/layouts/footer.php'; ?>