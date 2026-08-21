<?php include '../../../layouts/header.php'; ?>

<!-- Categories Section -->
<section id="categorias" class="categories-section container my-5 position-relative">
    <a href="../../perfumes.php" class="btn btn-outline-dark position-absolute" style="top: 0; left: 15px; font-weight: 600; font-size: 1.1rem; padding: 10px 20px;">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
    
    <h2 class="text-center mb-4">Perfumes Hombre</h2>

    <div class="categories-grid" style="display: flex; justify-content: center; gap: 2rem;">
        <a href="1.1_hombre.php" class="category-card" style="flex: 0 1 calc(33.333% - 2rem); max-width: 400px;">
            <div class="category-img-container">
                <img src="../../../../img/perfume.webp" alt="Perfumes 1.1" class="category-img">
            </div>
            <h3 class="category-title">Perfumes 1.1</h3>
        </a>
        
        <a href="original_hombre.php" class="category-card" style="flex: 0 1 calc(33.333% - 2rem); max-width: 400px;">
            <div class="category-img-container">
                <img src="../../../../img/perfumes/Khamrah.webp" alt="Perfumes Originales" class="category-img">
            </div>
            <h3 class="category-title">Perfumes Originales</h3>
        </a>
    </div>
</section>

<link href="/controlux/public/css/style.css" rel="stylesheet">

<?php include '../../../layouts/footer.php'; ?>


