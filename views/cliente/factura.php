<?php
session_start();

if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/database.php';

$id_pedido = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$id_usuario = $_SESSION['id_usuario'];

if ($id_pedido === 0) {
    die("Pedido no válido.");
}

// Obtener detalles del pedido y asegurar que pertenezca al usuario actual
$stmt_pedido = $conn->prepare("SELECT * FROM pedidos WHERE id_pedido = ? AND id_usuario = ?");
$stmt_pedido->execute([$id_pedido, $id_usuario]);
$pedido = $stmt_pedido->fetch(PDO::FETCH_ASSOC);

if (!$pedido) {
    die("Pedido no encontrado o no tienes permiso para verlo.");
}

// Obtener datos del cliente
$stmt_cliente = $conn->prepare("SELECT nombre, apellido, correo, telefono, direccion, ciudad, barrio, cedula FROM usuarios WHERE id_usuario = ?");
$stmt_cliente->execute([$id_usuario]);
$cliente = $stmt_cliente->fetch(PDO::FETCH_ASSOC);

// Obtener los productos del pedido
$stmt_detalles = $conn->prepare("
    SELECT dp.cantidad, dp.precio_unitario, dp.subtotal, pr.nombre, pr.referencia, c.nombre as categoria
    FROM detalle_pedidos dp
    JOIN productos pr ON dp.id_producto = pr.id_producto
    LEFT JOIN categorias c ON pr.id_categoria = c.id_categoria
    WHERE dp.id_pedido = ?
");
$stmt_detalles->execute([$id_pedido]);
$detalles = $stmt_detalles->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Virtual #<?php echo str_pad($id_pedido, 5, '0', STR_PAD_LEFT); ?> - JC URBAN</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    
    <link href="/controlux/public/css/style_factura.css?v=<?php echo time(); ?>" rel="stylesheet">
</head>
<body>

<div class="container">
    <div class="invoice-container">
        <!-- Botones de Acción -->
        <div class="d-flex justify-content-between mb-4 no-print" data-html2canvas-ignore>
            <a href="../../public/index.php" class="btn btn-outline-dark"><i class="bi bi-arrow-left"></i> Volver a la Tienda</a>
        </div>

        <div class="invoice-header d-flex justify-content-between align-items-end">
            <div>
                <h1 class="invoice-title m-0">JC URBAN</h1>
                <p class="text-muted m-0 mt-1" style="font-size: 0.9rem; letter-spacing: 3px;">EXCLUSIVIDAD & LUJO URBANO</p>
            </div>
            <div class="text-end">
                <h3 class="fw-bold m-0 text-uppercase">Factura Virtual</h3>
                <p class="text-muted m-0">N° Pedido: <strong class="text-dark">#<?php echo str_pad($id_pedido, 5, '0', STR_PAD_LEFT); ?></strong></p>
                <p class="text-muted m-0">Fecha: <strong class="text-dark"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></strong></p>
            </div>
        </div>

        <div class="row mb-5">
            <div class="col-sm-6 company-details">
                <h4>Datos de la Tienda</h4>
                <p><strong>JC URBAN</strong></p>
                <p>Neiva - Huila, Colombia</p>
                <p>Tel: +57 321 232 7275</p>
                <p>Email: jc.urban.2007@gmail.com</p>
            </div>
            <div class="col-sm-6 client-details text-sm-end mt-4 mt-sm-0">
                <h4>Datos del Cliente</h4>
                <p><strong><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></strong></p>
                <?php if (!empty($cliente['cedula'])): ?>
                    <p>C.C / NIT: <?php echo htmlspecialchars($cliente['cedula']); ?></p>
                <?php endif; ?>
                <p><?php echo htmlspecialchars($cliente['direccion']); ?> <?php echo !empty($cliente['barrio']) ? '- ' . htmlspecialchars($cliente['barrio']) : ''; ?></p>
                <p><?php echo htmlspecialchars($cliente['ciudad']); ?></p>
                <p>Tel: <?php echo htmlspecialchars($cliente['telefono']); ?></p>
                <p>Email: <?php echo htmlspecialchars($cliente['correo']); ?></p>
            </div>
        </div>

        <table class="table-invoice">
            <thead>
                <tr>
                    <th>Producto</th>
                    <th class="text-center">Cant.</th>
                    <th class="text-end">Precio Unit.</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles as $item): ?>
                <tr>
                    <td>
                        <strong class="fw-bold"><?php echo htmlspecialchars($item['nombre']); ?></strong><br>
                        <small class="text-muted" style="font-size: 0.8rem;">
                            <?php echo htmlspecialchars($item['categoria'] ?? 'General'); ?> | Ref: <?php echo htmlspecialchars($item['referencia']); ?>
                        </small>
                    </td>
                    <td class="text-center" style="vertical-align: middle;"><?php echo $item['cantidad']; ?></td>
                    <td class="text-end" style="vertical-align: middle;">$<?php echo number_format($item['precio_unitario'], 0, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="row justify-content-end mt-4">
            <div class="col-md-5">
                <div class="invoice-total-container d-flex justify-content-between align-items-center">
                    <span class="text-uppercase fw-bold text-muted">Total a Pagar</span>
                    <span class="invoice-total"><span>$</span><?php echo number_format($pedido['total'], 0, ',', '.'); ?></span>
                </div>
                <p class="text-muted text-center mt-3" style="font-size: 0.85rem;">Gracias por confiar en JC URBAN. Esta es una factura generada electrónicamente como soporte de tu compra.</p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const element = document.querySelector('.invoice-container');
        const opt = {
            margin:       10,
            filename:     'Factura_JCURBAN_#<?php echo str_pad($id_pedido, 5, '0', STR_PAD_LEFT); ?>.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2 },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' }
        };
        
        // Generar y descargar el PDF automáticamente
        html2pdf().set(opt).from(element).save();
    });
</script>
</body>
</html>
