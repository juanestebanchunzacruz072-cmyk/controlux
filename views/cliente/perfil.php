<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /controlux/views/auth/login.php");
    exit;
}

if (isset($_SESSION['usuario']['id_rol']) && $_SESSION['usuario']['id_rol'] == '1') {
    header("Location: /controlux/public/index.php");
    exit;
}

require_once '../../config/database.php';

// Obtener info actual del usuario
$id_usuario = $_SESSION['id_usuario'];
$usuario = [];
try {
    $stmt = $conn->prepare("SELECT id_rol, nombre, apellido, correo, telefono, direccion, ciudad, cedula FROM usuarios WHERE id_usuario = ?");
    $stmt->execute([$id_usuario]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error al obtener la información del usuario.");
}

// Paginación para pedidos
$items_por_pagina = 3; // Mostrar 3 pedidos por página para que no baje mucho
$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

$pedidos = [];
$total_pedidos = 0;
$total_paginas = 1;

try {
    // 1. Obtener el total de pedidos para calcular las páginas
    $stmt_count = $conn->prepare("SELECT COUNT(*) FROM pedidos WHERE id_usuario = ?");
    $stmt_count->execute([$id_usuario]);
    $total_pedidos = $stmt_count->fetchColumn();
    $total_paginas = ceil($total_pedidos / $items_por_pagina);

    // 2. Obtener los pedidos con límite
    $stmt = $conn->prepare("
        SELECT p.id_pedido, p.fecha_pedido, p.total, e.nombre as estado 
        FROM pedidos p 
        LEFT JOIN estado_pedidos e ON p.id_estado = e.id_estado 
        WHERE p.id_usuario = ? 
        ORDER BY p.id_pedido DESC
        LIMIT $items_por_pagina OFFSET $offset
    ");
    $stmt->execute([$id_usuario]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // 3. Obtener artículos para cada pedido
    foreach ($pedidos as &$pedido) {
        $stmt_items = $conn->prepare("
            SELECT dp.cantidad, pr.nombre 
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.id_producto = pr.id_producto
            WHERE dp.id_pedido = ?
        ");
        $stmt_items->execute([$pedido['id_pedido']]);
        $pedido['items'] = $stmt_items->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    // Error silencioso si no hay tablas
}

include '../layouts/header.php';
?>

<div class="container my-5" style="min-height: 60vh;">
    <!-- Botón Volver -->
    <div class="mb-4">
        <?php if ($usuario['id_rol'] == 1): ?>
            <a href="/controlux/views/admin/dashboard_admin.php" class="btn text-dark fw-bold" style="background-color: var(--gold, #D4AF37);">
                <i class="bi bi-arrow-left"></i> Volver al Dashboard
            </a>
        <?php else: ?>
            <a href="/controlux/public/index.php" class="btn text-dark fw-bold" style="background-color: var(--gold, #D4AF37);">
                <i class="bi bi-arrow-left"></i> Volver a la Tienda
            </a>
        <?php endif; ?>
    </div>
    
    <div class="row <?php echo ($usuario['id_rol'] == 1) ? 'justify-content-center' : ''; ?>">
        <!-- Columna de Perfil -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0" style="background-color: #ffffff; border: 2px solid var(--gold, #D4AF37) !important;">
                <div class="card-header text-center" style="background-color: transparent; border-bottom: 2px solid var(--gold, #D4AF37);">
                    <h3 class="mb-0" style="color: #000; font-family: 'Montserrat', sans-serif; font-weight: 700;">
                        <?php echo ($usuario['id_rol'] == 1) ? 'PERFIL ADMINISTRADOR' : 'MI PERFIL'; ?>
                    </h3>
                </div>
                <div class="card-body p-4" style="color: #333;">
                    
                    <?php if (isset($_GET['exito'])): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert" style="background-color: #198754; color: white; border: none;">
                            Tu información ha sido actualizada correctamente.
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($_GET['error'])): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Hubo un error al intentar actualizar tu información.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <form action="../../controllers/cliente/PerfilController.php" method="POST">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Nombre</label>
                                <input type="text" class="form-control" style="background-color: #f8f9fa; color: #555; border: 1px solid var(--gold, #D4AF37);" value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Apellido</label>
                                <input type="text" class="form-control" style="background-color: #f8f9fa; color: #555; border: 1px solid var(--gold, #D4AF37);" value="<?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Correo Electrónico</label>
                                <input type="email" class="form-control" style="background-color: #f8f9fa; color: #555; border: 1px solid var(--gold, #D4AF37);" value="<?php echo htmlspecialchars($usuario['correo'] ?? ''); ?>" readonly>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold">Cédula</label>
                                <input type="text" class="form-control" style="background-color: #f8f9fa; color: #555; border: 1px solid var(--gold, #D4AF37);" value="<?php echo htmlspecialchars($usuario['cedula'] ?? ''); ?>" readonly>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" class="form-control" style="background-color: #f8f9fa; color: #555; border: 1px solid var(--gold, #D4AF37);" value="<?php echo htmlspecialchars($usuario['telefono'] ?? ''); ?>" readonly>
                        </div>

                        <?php if ($usuario['id_rol'] == 2): ?>
                            <hr style="border-color: var(--gold, #D4AF37);">
                            <h5 class="mb-3 mt-4 fw-bold" style="color: var(--gold, #D4AF37);">Datos de Envío</h5>
                            <p class="small text-muted">Estos datos son los únicos que puedes modificar. Serán utilizados para el envío de tus pedidos.</p>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Dirección *</label>
                                <input type="text" name="direccion" class="form-control" style="background-color: #fff; color: #000; border: 1px solid var(--gold, #D4AF37);" value="<?php echo htmlspecialchars($usuario['direccion'] ?? ''); ?>" placeholder="Ej: Calle 123 # 45-67" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Ciudad *</label>
                                <input type="text" name="ciudad" class="form-control" style="background-color: #fff; color: #000; border: 1px solid var(--gold, #D4AF37);" value="<?php echo htmlspecialchars($usuario['ciudad'] ?? ''); ?>" placeholder="Ej: Bogotá" required>
                            </div>

                            <div class="mb-4">
                                <label class="form-label fw-bold">Barrio *</label>
                                <input type="text" name="barrio" class="form-control" style="background-color: #fff; color: #000; border: 1px solid var(--gold, #D4AF37);" value="<?php echo htmlspecialchars($usuario['barrio'] ?? ''); ?>" placeholder="Ej: Chapinero" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn py-2 fw-bold text-dark" style="background-color: var(--gold, #D4AF37);">
                                    ACTUALIZAR DATOS DE ENVÍO
                                </button>
                            </div>
                        <?php endif; ?>
                    </form>

                </div>
            </div>
        </div>

        <?php if ($usuario['id_rol'] == 2): ?>
        <!-- Columna de Pedidos (Solo para Clientes) -->
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0" style="background-color: #ffffff; border: 2px solid var(--gold, #D4AF37) !important;">
                <div class="card-header text-center" style="background-color: transparent; border-bottom: 2px solid var(--gold, #D4AF37);">
                    <h3 class="mb-0" style="color: #000; font-family: 'Montserrat', sans-serif; font-weight: 700;">MIS PEDIDOS</h3>
                </div>
                <div class="card-body p-4" style="color: #333;">
                    <?php if (empty($pedidos)): ?>
                        <div class="text-center py-5">
                            <i class="bi bi-box-seam" style="font-size: 3rem; color: #ccc;"></i>
                            <p class="mt-3 text-muted">Aún no has realizado ningún pedido.</p>
                            <a href="/controlux/public/index.php" class="btn btn-outline-dark fw-bold mt-2" style="border-color: var(--gold, #D4AF37);">Explorar Catálogo</a>
                        </div>
                    <?php else: ?>
                        <link href="/controlux/public/css/style_perfil.css?v=<?php echo time(); ?>" rel="stylesheet">

                        <?php foreach ($pedidos as $pedido): ?>
                            <div class="order-card">
                                <div class="order-header">
                                    <div>
                                        <h5 class="mb-1" style="font-weight: 800;">Pedido #<?php echo str_pad($pedido['id_pedido'], 6, '0', STR_PAD_LEFT); ?></h5>
                                        <small class="text-muted"><i class="bi bi-calendar3"></i> <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></small>
                                    </div>
                                    <h4 class="mb-0" style="color: var(--gold, #D4AF37); font-weight: 800;">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></h4>
                                </div>

                                <?php 
                                    $estado = strtolower($pedido['estado'] ?? 'pendiente');
                                    
                                    // Mapear estado a paso actual (1 a 4)
                                    $paso_actual = 1;
                                    if (strpos($estado, 'pagado') !== false) $paso_actual = 2;
                                    else if (strpos($estado, 'enviado') !== false) $paso_actual = 3;
                                    else if (strpos($estado, 'entregado') !== false) $paso_actual = 4;
                                ?>

                                <!-- Stepper Visual -->
                                <div class="stepper-wrapper">
                                    <div class="stepper-item <?php echo $paso_actual >= 1 ? ($paso_actual == 1 ? 'active' : 'completed') : ''; ?>">
                                        <div class="step-counter"><i class="bi <?php echo $paso_actual > 1 ? 'bi-check-lg' : 'bi-clipboard-check'; ?>"></i></div>
                                        <div class="step-name">Confirmado</div>
                                    </div>
                                    <div class="stepper-item <?php echo $paso_actual >= 2 ? ($paso_actual == 2 ? 'active' : 'completed') : ''; ?>">
                                        <div class="step-counter"><i class="bi <?php echo $paso_actual > 2 ? 'bi-check-lg' : 'bi-credit-card'; ?>"></i></div>
                                        <div class="step-name">Pagado</div>
                                    </div>
                                    <div class="stepper-item <?php echo $paso_actual >= 3 ? ($paso_actual == 3 ? 'active' : 'completed') : ''; ?>">
                                        <div class="step-counter"><i class="bi <?php echo $paso_actual > 3 ? 'bi-check-lg' : 'bi-truck'; ?>"></i></div>
                                        <div class="step-name">En Camino</div>
                                    </div>
                                    <div class="stepper-item <?php echo $paso_actual >= 4 ? 'completed active' : ''; ?>">
                                        <div class="step-counter"><i class="bi <?php echo $paso_actual == 4 ? 'bi-check-lg' : 'bi-house-door'; ?>"></i></div>
                                        <div class="step-name">Entregado</div>
                                    </div>
                                    <!-- Progress bar overlay for the line -->
                                    <div style="position: absolute; top: 15px; left: 0; height: 3px; background-color: var(--gold, #D4AF37); z-index: 1; transition: width 0.3s ease; width: <?php echo ($paso_actual - 1) * 33.33; ?>%;"></div>
                                </div>

                                <div class="mb-3">
                                    <strong style="color: #444;">Dirección de Entrega:</strong><br>
                                    <span style="color: #666;"><i class="bi bi-geo-alt" style="color: var(--gold, #D4AF37);"></i> <?php echo htmlspecialchars($usuario['direccion'] ?? 'Pendiente'); ?>, <?php echo htmlspecialchars($usuario['barrio'] ?? ''); ?>, <?php echo htmlspecialchars($usuario['ciudad'] ?? ''); ?></span>
                                </div>

                                <div class="order-details-box">
                                    <h6 class="fw-bold mb-3" style="color: #333;">Detalle del pedido</h6>
                                    <ul style="list-style: none; padding: 0; margin: 0;">
                                        <?php if (!empty($pedido['items'])): ?>
                                            <?php foreach ($pedido['items'] as $item): ?>
                                                <li style="display: flex; justify-content: space-between; border-bottom: 1px solid #eee; padding: 8px 0;">
                                                    <span style="color: #555;"><i class="bi bi-dot" style="color: var(--gold, #D4AF37);"></i> <?php echo htmlspecialchars($item['nombre']); ?></span>
                                                    <span class="fw-bold text-muted">x<?php echo htmlspecialchars($item['cantidad']); ?></span>
                                                </li>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <li class="text-muted">No hay artículos registrados para este pedido.</li>
                                        <?php endif; ?>
                                    </ul>
                                </div>
                            </div>
                        <?php endforeach; ?>

                        <!-- Paginación -->
                        <?php if ($total_paginas > 1): ?>
                            <nav aria-label="Navegación de pedidos" class="mt-4">
                                <ul class="pagination justify-content-center">
                                    <li class="page-item <?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $pagina_actual - 1; ?>" style="color: var(--gold, #D4AF37);">&laquo; Anterior</a>
                                    </li>
                                    
                                    <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                                        <li class="page-item <?php echo $pagina_actual == $i ? 'active' : ''; ?>">
                                            <a class="page-link" href="?page=<?php echo $i; ?>" 
                                               style="<?php echo $pagina_actual == $i ? 'background-color: var(--gold, #D4AF37); border-color: var(--gold, #D4AF37); color: #fff;' : 'color: #333;'; ?>">
                                                <?php echo $i; ?>
                                            </a>
                                        </li>
                                    <?php endfor; ?>
                                    
                                    <li class="page-item <?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                                        <a class="page-link" href="?page=<?php echo $pagina_actual + 1; ?>" style="color: var(--gold, #D4AF37);">Siguiente &raquo;</a>
                                    </li>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../layouts/footer.php'; ?>
