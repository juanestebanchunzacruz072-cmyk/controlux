<?php
session_start();

// Si no hay carrito, volver al index
if (!isset($_SESSION['carrito_temporal']) || empty($_SESSION['carrito_temporal'])) {
    header("Location: ../../public/index.php");
    exit;
}

// Si no está logueado, mandarlo al login
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

$carrito = $_SESSION['carrito_temporal'];
$subtotal = 0;
foreach ($carrito as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$total = $subtotal; // Asumiendo que no hay impuestos adicionales
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Detalle del Pedido</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style_detalle_pedido.css?v=<?php echo time(); ?>">
</head>
<body>
    <nav class="navbar navbar-custom">
        <div class="container d-flex justify-content-between align-items-center">
            <!-- Botón Cancelar a la izquierda -->
            <a href="../../controllers/cliente/CarritoController.php?accion=limpiar" class="btn btn-outline-light btn-sm" style="border-color: #555;" onclick="localStorage.removeItem('controlux_cart');">
                <i class="bi bi-x-circle me-1"></i> Volver y Cancelar
            </a>

            <a class="navbar-brand d-flex align-items-center m-0" href="../../public/index.php">
                JC URBAN - CHECKOUT
            </a>

            <!-- Usuario Logueado a la derecha -->
            <div class="text-white" style="font-size: 0.9rem;">
                <i class="bi bi-person-circle me-1" style="color: var(--gold);"></i> 
                <?php echo htmlspecialchars($_SESSION['usuario']['usuario'] ?? 'Cliente'); ?>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="checkout-container">
            <h2 class="checkout-title">Resumen de tu Pedido</h2>
            
            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="text-center">Cantidad</th>
                            <th class="text-end">Precio U.</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($carrito as $item): ?>
                        <tr>
                            <td class="fw-bold">
                                <div class="d-flex align-items-center">
                                    <?php if(isset($item['img']) && !empty($item['img'])): ?>
                                        <img src="<?php echo htmlspecialchars($item['img']); ?>" alt="<?php echo htmlspecialchars($item['name']); ?>" style="width: 50px; height: 50px; object-fit: contain;" class="me-3 border rounded">
                                    <?php else: ?>
                                        <div class="product-img-placeholder me-3">
                                            <i class="bi bi-image"></i>
                                        </div>
                                    <?php endif; ?>
                                    <span><?php echo htmlspecialchars($item['name']); ?></span>
                                </div>
                            </td>
                            <td class="text-center"><?php echo $item['quantity']; ?></td>
                            <td class="text-end">$<?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                            <td class="text-end fw-bold">$<?php echo number_format($item['price'] * $item['quantity'], 0, ',', '.'); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-7 col-md-8">
                    <div class="summary-box">
                        <div class="summary-row">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                        </div>
                        <div class="summary-row total">
                            <span>Total a Pagar:</span>
                            <span style="color: var(--gold);">$<?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>

                        <form action="../../controllers/cliente/PedidoClienteController.php?accion=guardar" method="POST" class="mt-4" target="_blank" onsubmit="setTimeout(() => { window.location.href = '../../public/index.php?pedido_exito=1'; }, 1000); localStorage.removeItem('controlux_cart');">
                            <input type="hidden" name="total" value="<?php echo $total; ?>">
                            <button type="submit" class="btn btn-confirm">
                                <i class="bi bi-whatsapp me-2"></i> Confirmar por WhatsApp
                            </button>
                            <a href="../../public/index.php" class="btn-back">
                                <i class="bi bi-plus-circle me-2"></i> Agregar más productos
                            </a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
