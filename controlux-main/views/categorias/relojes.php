<?php include '../../views/layouts/header.php'; ?>

<!-- Categories Section -->
<section id="categorias" class="categories-section container my-5 position-relative">
    <a href="../../public/index.php#categorias" class="btn btn-outline-dark position-absolute" style="top: 0; left: 15px; font-weight: 600; font-size: 1.1rem; padding: 10px 20px;">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
    
    <h2 class="text-center mb-4">Relojes</h2>

    <div class="categories-grid" style="display: flex; justify-content: center; gap: 2rem; flex-wrap: wrap;">
        <a href="subcategorias/relojes/originales.php" class="category-card" style="flex: 0 1 calc(33% - 2rem); min-width: 250px; max-width: 350px;">
            <div class="category-img-container" style="height: 280px; display: flex; align-items: center; justify-content: center;">
                <img src="../../img/relojes/Audemars-Piguet-Royal.webp" alt="Originales" class="category-img" style="max-height: 100%; width: auto; max-width: 100%; object-fit: contain;">
            </div>
            <h3 class="category-title" style="font-size: 1.6rem; margin-top: auto; text-align: center; width: 100%; font-weight: bold;">Relojes Originales</h3>
        </a>
        
        <a href="subcategorias/relojes/altagama.php" class="category-card" style="flex: 0 1 calc(33% - 2rem); min-width: 250px; max-width: 350px;">
            <div class="category-img-container" style="height: 280px; display: flex; align-items: center; justify-content: center;">
                <img src="../../img/reloj.webp" alt="Alta Gama" class="category-img" style="max-height: 100%; width: auto; max-width: 100%; object-fit: contain; transform: scale(1.2);">
            </div>
            <h3 class="category-title" style="font-size: 1.6rem; margin-top: auto; text-align: center; width: 100%; font-weight: bold;">Relojes Alta Gama</h3>
        </a>

        <a href="subcategorias/relojes/Replicas.php" class="category-card" style="flex: 0 1 calc(33% - 2rem); min-width: 250px; max-width: 350px;">
            <div class="category-img-container" style="height: 280px; display: flex; align-items: center; justify-content: center;">
                <img src="../../img/relojes/richard-mille.jpg" alt="Réplicas" class="category-img" style="max-height: 100%; width: auto; max-width: 100%; object-fit: contain;">
            </div>
            <h3 class="category-title" style="font-size: 1.6rem; margin-top: auto; text-align: center; width: 100%; font-weight: bold;">Relojes Réplicas</h3>
        </a>
    </div>
</section>

<link href="/controlux/public/css/style.css" rel="stylesheet">

<?php include '../../views/layouts/footer.php'; ?>