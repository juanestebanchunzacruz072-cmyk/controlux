<?php 
require_once '../../../../models/Producto.php';
include '../../../layouts/header.php'; 

try {
    $modeloProducto = new Producto();
    $productos = $modeloProducto->obtenerCatalogo('Accesorios', 'Correa');
} catch (Exception $e) {
    $productos = [];
}
?>

<section class="container my-5 position-relative">
    <a href="../../accesorios.php" class="btn btn-outline-dark position-absolute" style="top: 0; left: 15px; font-weight: 600; font-size: 1.1rem; padding: 10px 20px;">
        <i class="bi bi-arrow-left"></i> Volver
    </a>
    
    <h2 class="text-center mb-5 fw-bold" style="font-size: 2.5rem;">Correas</h2>

    <div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4 justify-content-center mt-2">
        <?php if (empty($productos)): ?>
            <div class="col-12 text-center my-5">
                <i class="bi bi-inboxes" style="font-size: 3rem; color: #ccc;"></i>
                <h4 style="color: #666; margin-top: 15px;">Aún no hay productos en esta categoría.</h4>
            </div>
        <?php else: ?>
            <?php foreach ($productos as $p): ?>
                <div class="col">
                    <div class="card h-100 product-card open-product-modal" style="cursor: pointer;" data-desc="<?php echo htmlspecialchars($p['descripcion'] ?? 'Sin descripción'); ?>" data-brand="<?php echo htmlspecialchars($p['marca'] ?? 'Desconocida'); ?>" data-stock="<?php echo $p['stock']; ?>">
                        <div class="p-3 d-flex justify-content-center align-items-center" style="height: 250px;">
                            <img src="/controlux/<?php echo htmlspecialchars(ltrim($p['url_imagen'] ?? 'img/accesorio.png', '/')); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($p['nombre']); ?>" style="max-height: 100%; width: auto; object-fit: contain;">
                        </div>
                        <div class="card-body d-flex flex-column text-center">
                            <h5 class="card-title mb-3"><?php echo htmlspecialchars($p['nombre']); ?></h5>
                            <p class="card-text mb-4" style="color: var(--gold-premium); font-weight: bold; font-size: 1.2rem;">$ <?php echo number_format($p['precio'], 0, ',', '.'); ?></p>
                            
                            <?php if($p['stock'] > 0): ?>
                                <button class="btn btn-gold w-100 mt-auto add-to-cart-btn" 
                                        data-id="<?php echo $p['id_producto']; ?>" 
                                        data-name="<?php echo htmlspecialchars($p['nombre']); ?>" 
                                        data-price="<?php echo $p['precio']; ?>" 
                                        data-stock="<?php echo $p['stock']; ?>" 
                                        data-img="/controlux/<?php echo htmlspecialchars(ltrim($p['url_imagen'] ?? 'img/accesorio.png', '/')); ?>">
                                    <i class="bi bi-cart-plus me-2"></i>Agregar al carrito
                                </button>
                            <?php else: ?>
                                <button class="btn btn-secondary w-100 mt-auto" disabled>
                                    <i class="bi bi-x-circle me-2"></i>Agotado
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>

<link href="/controlux/public/css/style.css" rel="stylesheet">
<?php include '../../../layouts/footer.php'; ?>
