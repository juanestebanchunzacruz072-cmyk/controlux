<?php include '../../views/layouts/header.php'; ?>

<!-- Categories Section -->
<section id="categorias" class="categories-section container my-5 position-relative">
    <div class="header-cat-container d-flex align-items-center justify-content-center position-relative mb-4">
    <a href="../../public/index.php#categorias" class="btn btn-outline-dark btn-volver position-absolute start-0" style="font-weight: 600; font-size: 1.1rem; padding: 10px 20px; z-index: 10;">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
    <h2 class="text-center m-0">Accesorios</h2>
</div>

    <div class="categories-grid" style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap; margin-top: 2rem;">
        <a href="subcategorias/accesorios/billeteras.php" class="category-card" style="flex: 0 1 calc(25% - 2rem); min-width: 200px; max-width: 300px;">
            <div class="category-img-container" style="height: 300px; display: flex; align-items: center; justify-content: center; padding: 10px;">
                <img src="../../img/accesorios/billeteras.webp" alt="Billeteras" class="category-img" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h3 class="category-title">Billeteras</h3>
        </a>
        
        <a href="subcategorias/accesorios/carrieles.php" class="category-card" style="flex: 0 1 calc(25% - 2rem); min-width: 200px; max-width: 300px;">
            <div class="category-img-container" style="height: 300px; display: flex; align-items: center; justify-content: center; padding: 10px;">
                <img src="../../img/accesorios/carrieles.webp" alt="Carrieles" class="category-img" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h3 class="category-title">Carrieles</h3>
        </a>

        <a href="subcategorias/accesorios/correas.php" class="category-card" style="flex: 0 1 calc(25% - 2rem); min-width: 200px; max-width: 300px;">
            <div class="category-img-container" style="height: 300px; display: flex; align-items: center; justify-content: center; padding: 10px;">
                <img src="../../img/accesorio.png" alt="Correas" class="category-img" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h3 class="category-title">Correas</h3>
        </a>

        <a href="subcategorias/accesorios/gorras.php" class="category-card" style="flex: 0 1 calc(25% - 2rem); min-width: 200px; max-width: 300px;">
            <div class="category-img-container" style="height: 300px; display: flex; align-items: center; justify-content: center; padding: 10px;">
                <img src="../../img/accesorios/gorra.webp" alt="Gorras" class="category-img" style="width: 100%; height: 100%; object-fit: contain;">
            </div>
            <h3 class="category-title">Gorras</h3>
        </a>
    </div>
</section>

<link href="/controlux/public/css/style.css?v=<?php echo time(); ?>" rel="stylesheet">

<?php include '../../views/layouts/footer.php'; ?>
