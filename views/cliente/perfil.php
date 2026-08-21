<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: /controlux/views/auth/login.php");
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

// Obtener pedidos del usuario
$pedidos = [];
try {
    $stmt = $conn->prepare("
        SELECT p.id_pedido, p.fecha_pedido, p.total, e.nombre as estado 
        FROM pedidos p 
        LEFT JOIN estado_pedidos e ON p.id_estado = e.id_estado 
        WHERE p.id_usuario = ? 
        ORDER BY p.id_pedido DESC
    ");
    $stmt->execute([$id_usuario]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Obtener artículos para cada pedido
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
    // Si la tabla no existe o hay error, pedidos quedará vacío
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
                        <div class="table-responsive">
                            <table class="table align-middle">
                                <thead>
                                    <tr>
                                        <th style="color: #555;">ID Pedido</th>
                                        <th style="color: #555;">Fecha</th>
                                        <th style="color: #555;">Total</th>
                                        <th style="color: #555;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pedidos as $pedido): ?>
                                        <tr>
                                            <td class="fw-bold">#<?php echo htmlspecialchars($pedido['id_pedido']); ?></td>
                                            <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                                            <td class="fw-bold" style="color: var(--gold, #D4AF37);">$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></td>
                                            <td>
                                                <?php 
                                                    $estado = $pedido['estado'] ?? 'Recibido';
                                                    $badgeClass = 'bg-secondary';
                                                    if (strtolower($estado) == 'entregado') $badgeClass = 'bg-success';
                                                    else if (strtolower($estado) == 'enviado') $badgeClass = 'bg-primary';
                                                    else if (strtolower($estado) == 'empacado') $badgeClass = 'bg-info text-dark';
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>"><?php echo htmlspecialchars($estado); ?></span>
                                            </td>
                                        </tr>
                                        <?php if (!empty($pedido['items'])): ?>
                                        <tr>
                                            <td colspan="4" style="background-color: #f8f9fa; border-top: none; padding: 10px 15px;">
                                                <div style="font-size: 0.85rem; color: #666;">
                                                    <strong>Artículos:</strong>
                                                    <ul class="mb-0 mt-1" style="list-style-type: none; padding-left: 0;">
                                                        <?php foreach ($pedido['items'] as $item): ?>
                                                            <li><i class="bi bi-arrow-right-short" style="color: var(--gold, #D4AF37);"></i> <?php echo htmlspecialchars($item['cantidad']); ?>x <?php echo htmlspecialchars($item['nombre']); ?></li>
                                                        <?php endforeach; ?>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

<?php include '../layouts/footer.php'; ?>
