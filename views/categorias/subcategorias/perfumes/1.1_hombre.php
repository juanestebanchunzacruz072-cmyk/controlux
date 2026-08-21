<?php 
require '../../../../config/database.php';
include '../../../layouts/header.php'; 

// Fetch Hombre & Unisex 1.1 Perfumes
try {
    $stmt = $conn->query("
        SELECT p.id_producto, p.nombre, p.precio, p.genero, p.stock, p.descripcion, m.nombre as marca, p.img 
        FROM productos p
        INNER JOIN categorias c ON p.id_categoria = c.id_categoria
        INNER JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
        LEFT JOIN marcas m ON p.id_marca = m.id_marca
        WHERE c.nombre = 'Perfumes' 
          AND s.nombre = '1.1' 
          AND (p.genero = 'Hombre' OR p.genero = 'Unisex')
          AND p.activo = 1
        ORDER BY p.id_producto DESC
    ");
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $productos = [];
}
?>

<!-- Content Section -->
<section class="container my-5 position-relative">
    <a href="perfumes_hombres.php" class="btn btn-outline-dark position-absolute" style="top: 0; left: 15px; font-weight: 600; font-size: 1.1rem; padding: 10px 20px; z-index: 10;">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
    
    <h2 class="text-center mb-5 mt-4" style="font-weight: 700; padding-top: 20px;">Perfumes 1.1 <span style="color: var(--gold, #D4AF37);">Hombre</span></h2>

    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 mt-2">
        <?php if (empty($productos)): ?>
            <div class="col-12 text-center my-5">
                <i class="bi bi-inboxes" style="font-size: 3rem; color: #ccc;"></i>
                <h4 style="color: #666; margin-top: 15px;">Aún no hemos agregado perfumes en esta categoría.</h4>
            </div>
        <?php else: ?>
            <?php foreach ($productos as $p): ?>
                <div class="col">
                    <div class="card h-100 product-card open-product-modal shadow-sm border-0" style="border-radius: 12px; overflow: hidden; transition: all 0.3s ease;" style="cursor: pointer;" data-desc="<?php echo htmlspecialchars($p['descripcion'] ?? 'Sin descripción'); ?>" data-brand="<?php echo htmlspecialchars($p['marca'] ?? 'Desconocida'); ?>" data-stock="<?php echo $p['stock']; ?>">
                        <div style="background: #f8f9fa; padding: 20px; text-align: center; position: relative;">
                            <?php if($p['genero'] == 'Unisex'): ?>
                                <span class="badge bg-secondary position-absolute top-0 start-0 m-2">Unisex</span>
                            <?php endif; ?>
                            <img src="/controlux/<?php echo htmlspecialchars(ltrim($p['img'] ?? 'img/perfume.webp', '/')); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['nombre']); ?>" style="height: 200px; object-fit: contain;">
                        </div>
                        <div class="card-body text-center d-flex flex-column">
                            <span class="text-uppercase mb-1" style="font-size: 0.8rem; letter-spacing: 1px; color: #888; font-weight: 600;"><?php echo htmlspecialchars($p['marca'] ?? 'Marca'); ?></span>
                            <h5 class="card-title fw-bold" style="font-size: 1.1rem; flex-grow: 1;"><?php echo htmlspecialchars($p['nombre']); ?></h5>
                            <p class="card-text my-3" style="font-size: 1.4rem; color: var(--gold, #D4AF37); font-weight: 700;">$<?php echo number_format($p['precio'], 0, ',', '.'); ?></p>
                            
                            <?php if($p['stock'] > 0): ?>
                                <button type="button" class="btn btn-dark w-100 rounded-pill py-2 add-to-cart-btn" 
                                    data-id="<?php echo $p['id_producto']; ?>" 
                                    data-name="<?php echo htmlspecialchars($p['nombre']); ?>" 
                                    data-price="<?php echo $p['precio']; ?>" 
                                    data-stock="<?php echo $p['stock']; ?>" 
                                    data-img="/controlux/<?php echo htmlspecialchars(ltrim($p['img'] ?? 'img/perfume.webp', '/')); ?>"
                                    style="font-weight: 600; font-size: 0.95rem;">
                                    <i class="bi bi-cart-plus me-1"></i> Añadir al carrito
                                </button>
                            <?php else: ?>
                                <button type="button" class="btn w-100 rounded-pill py-2" style="background-color: #e9ecef; color: #6c757d; font-weight: 600; font-size: 0.95rem; cursor: not-allowed;" disabled>
                                    <i class="bi bi-x-circle me-1"></i> Agotado
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<link rel="stylesheet" href="/controlux/public/css/style_catalogo.css?v=<?php echo time(); ?>">

<?php include '../../../layouts/footer.php'; ?>


