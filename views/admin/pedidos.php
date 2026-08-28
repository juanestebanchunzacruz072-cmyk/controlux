<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/database.php';

// Filtros y Paginación
$filtro_fecha = $_GET['fecha'] ?? '';
$filtro_id_pedido = trim($_GET['id_pedido'] ?? '');
$items_por_pagina = 10;
$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

$pedidos = [];
$total_paginas = 1;

try {
    // Base WHERE
    $where = "WHERE 1=1";
    $params = [];
    if (!empty($filtro_fecha)) {
        $where .= " AND DATE(p.fecha_pedido) = :fecha";
        $params[':fecha'] = $filtro_fecha;
    }
    if (!empty($filtro_id_pedido)) {
        $where .= " AND p.id_pedido = :id_pedido";
        $params[':id_pedido'] = $filtro_id_pedido;
    }

    // Contar total
    $sql_count = "SELECT COUNT(*) FROM pedidos p $where";
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->execute($params);
    $total_pedidos = $stmt_count->fetchColumn();
    $total_paginas = ceil($total_pedidos / $items_por_pagina);

    // Obtener datos
    $sql = "
        SELECT p.id_pedido, p.fecha_pedido, p.total, p.direccion_entrega, 
               u.nombre, u.apellido, e.nombre as estado, e.id_estado
        FROM pedidos p 
        INNER JOIN usuarios u ON p.id_usuario = u.id_usuario
        INNER JOIN estado_pedidos e ON p.id_estado = e.id_estado
        $where
        ORDER BY p.id_pedido DESC
        LIMIT " . (int)$items_por_pagina . " OFFSET " . (int)$offset;
        
    $stmt = $conn->prepare($sql);
    $stmt->execute($params);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener detalles (productos) para cada pedido
    foreach ($pedidos as &$pedido) {
        $stmt_det = $conn->prepare("
            SELECT dp.cantidad, dp.precio_unitario, pr.nombre, pr.referencia, s.nombre as subcategoria
            FROM detalle_pedidos dp
            JOIN productos pr ON dp.id_producto = pr.id_producto
            LEFT JOIN subcategoria s ON pr.id_subcategoria = s.id_subcategoria
            WHERE dp.id_pedido = ?
        ");
        $stmt_det->execute([$pedido['id_pedido']]);
        $pedido['detalles'] = $stmt_det->fetchAll(PDO::FETCH_ASSOC);
    }
} catch (PDOException $e) {
    $error = "No se pudieron cargar los pedidos. " . $e->getMessage();
}

// Obtener la lista de estados para el select
$estados = [];
try {
    $stmt = $conn->query("SELECT * FROM estado_pedidos ORDER BY id_estado ASC");
    $estados = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Gestión de Pedidos</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style_dashboard_admin.css?v=<?php echo time(); ?>">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../public/css/style_productos_admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../public/css/pagination.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Sidebar -->
    <?php include '../layouts/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar" style="display: flex; align-items: center; flex-wrap: wrap; gap: 15px;">
            <h1 style="margin: 0;">GESTIÓN DE PEDIDOS</h1>
            
            <!-- Filtro compacto en el topbar -->
            <form method="GET" action="pedidos.php" style="display: flex; gap: 8px; align-items: center; margin-left: auto; margin-right: 15px; flex-wrap: wrap; justify-content: flex-end;">
                <input type="number" name="id_pedido" value="<?php echo htmlspecialchars($filtro_id_pedido); ?>" placeholder="Cod. Pedido..." style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; outline: none; height: 38px; box-sizing: border-box; width: 120px;">
                <input type="date" name="fecha" value="<?php echo htmlspecialchars($filtro_fecha); ?>" style="padding: 8px 12px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Montserrat', sans-serif; font-size: 0.85rem; outline: none; height: 38px; box-sizing: border-box;">
                <button type="submit" title="Buscar" style="height: 38px; padding: 0 15px; border-radius: 6px; border: none; font-weight: 700; cursor: pointer; display: flex; align-items: center; background-color: var(--gold, #D4AF37); color: #111; transition: background 0.3s;">
                    <i class="bi bi-search"></i>
                </button>
                <?php if(!empty($filtro_fecha) || !empty($filtro_id_pedido)): ?>
                <a href="pedidos.php" title="Limpiar filtro" style="height: 38px; padding: 0 15px; border-radius: 6px; border: 1px solid #ccc; font-weight: 700; cursor: pointer; display: flex; align-items: center; background-color: #fff; color: #333; text-decoration: none; transition: background 0.3s;">
                    <i class="bi bi-x-circle"></i>
                </a>
                <?php endif; ?>
            </form>

            <div class="topbar-actions" style="margin: 0;">
                <a href="dashboard_admin.php" class="btn-outline-dark-custom">
                    <i class="bi bi-arrow-left"></i> VOLVER
                </a>
            </div>
        </header>

        <section class="table-container-section mt-4">
            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title">HISTORIAL DE PEDIDOS</h3>
            </div>
            
            <?php if (isset($error)): ?>
                <div style="background: #f8d7da; color: #842029; padding: 15px; border-radius: 5px; margin-bottom: 20px;">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID PEDIDO</th>
                            <th>CLIENTE</th>
                            <th>FECHA</th>
                            <th>DIRECCIÓN ENTREGA</th>
                            <th>TOTAL</th>
                            <th>ESTADO ACTUAL</th>
                            <th>CAMBIAR ESTADO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pedidos)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">Aún no se han registrado pedidos en la tienda.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pedidos as $p): ?>
                            <tr>
                                <td><strong>#<?php echo str_pad($p['id_pedido'], 5, '0', STR_PAD_LEFT); ?></strong></td>
                                <td><?php echo htmlspecialchars($p['nombre'] . ' ' . $p['apellido']); ?></td>
                                <td style="white-space: nowrap;"><?php echo date('d/m/Y H:i', strtotime($p['fecha_pedido'])); ?></td>
                                <td><?php echo htmlspecialchars($p['direccion_entrega'] ?? 'Pendiente'); ?></td>
                                <td class="price-col">$ <?php echo number_format($p['total'], 0, ',', '.'); ?></td>
                                <td>
                                    <span class="estado-badge estado-<?php echo $p['id_estado']; ?>">
                                        <?php echo htmlspecialchars($p['estado']); ?>
                                    </span>
                                </td>
                                <td>
                                    <div style="display: flex; gap: 10px; align-items: center;">
                                        <form action="../../controllers/Admin/PedidoController.php?accion=cambiarEstado" method="POST" style="display: flex; flex-direction: row; flex-wrap: nowrap; align-items: center; gap: 5px; margin: 0; padding: 0;">
                                            <input type="hidden" name="id_pedido" value="<?php echo $p['id_pedido']; ?>">
                                            <select name="id_estado" class="select-estado" style="margin: 0; flex-shrink: 1;">
                                                <?php foreach ($estados as $est): ?>
                                                    <?php $disabled = ($est['id_estado'] < $p['id_estado']) ? 'disabled' : ''; ?>
                                                    <option value="<?php echo $est['id_estado']; ?>" <?php echo ($est['id_estado'] == $p['id_estado']) ? 'selected' : ''; ?> <?php echo $disabled; ?>>
                                                        <?php echo htmlspecialchars($est['nombre']); ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button type="submit" class="btn-update" style="margin: 0; flex-shrink: 0;" title="Actualizar estado"><i class="bi bi-check2"></i></button>
                                        </form>
                                        
                                        <button type="button" class="btn btn-sm btn-info btn-ver-detalles" 
                                            style="padding: 4px 10px; border-radius: 4px; display: flex; align-items: center; justify-content: center; background-color: #111; border: none; color: white;" 
                                            title="Ver Detalles"
                                            data-id="<?php echo $p['id_pedido']; ?>"
                                            data-detalles='<?php echo json_encode($p['detalles']); ?>'>
                                            <i class="bi bi-eye"></i>
                                        </button>

                                        <?php if ($p['id_estado'] == 1): ?>
                                            <button type="button" class="btn btn-sm" onclick="confirmarEliminar(<?php echo $p['id_pedido']; ?>)" title="Eliminar Pedido" style="padding: 4px 10px; border-radius: 4px; display: flex; align-items: center; justify-content: center; background-color: #dc3545; border: none; color: white;">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                <nav aria-label="Navegación de pedidos" class="mt-4 mb-2">
                    <ul class="custom-pagination">
                        <?php 
                            $query_string = $_GET;
                            unset($query_string['page']);
                            $base_url = '?' . http_build_query($query_string) . (!empty($query_string) ? '&' : '');
                        ?>
                        <li class="<?php echo $pagina_actual <= 1 ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $base_url; ?>page=<?php echo $pagina_actual - 1; ?>">&laquo; Anterior</a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
                            <li class="<?php echo $pagina_actual == $i ? 'active' : ''; ?>">
                                <a class="page-link" href="<?php echo $base_url; ?>page=<?php echo $i; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="<?php echo $pagina_actual >= $total_paginas ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo $base_url; ?>page=<?php echo $pagina_actual + 1; ?>">Siguiente &raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        </section>

        <!-- Modal de Detalles del Pedido -->
        <div id="modalDetallesPedido" class="modal-overlay">
            <div class="modal-custom" style="max-width: 650px;">
                <button type="button" onclick="document.getElementById('modalDetallesPedido').style.display = 'none'" class="close-btn"><i class="bi bi-x-lg"></i></button>
                <h2 style="font-family: 'Montserrat', sans-serif; font-weight: 800; margin-bottom: 20px; font-size: 1.5rem; color: #111;">DETALLES DEL PEDIDO <span id="modal-pedido-id" style="color: var(--gold, #D4AF37);"></span></h2>
                
                <div class="table-container" style="border: 1px solid #eee; border-radius: 8px; padding: 0;">
                    <table class="table" style="margin: 0; width: 100%;">
                        <thead style="background-color: #f8f9fa;">
                            <tr>
                                <th style="padding: 12px 15px; font-size: 0.85rem;">PRODUCTO / REF</th>
                                <th style="padding: 12px 15px; font-size: 0.85rem; text-align: center;">CANT.</th>
                                <th style="padding: 12px 15px; font-size: 0.85rem; text-align: right;">PRECIO UNIT.</th>
                                <th style="padding: 12px 15px; font-size: 0.85rem; text-align: right;">SUBTOTAL</th>
                            </tr>
                        </thead>
                        <tbody id="modal-productos-list">
                            <!-- Los productos se inyectarán aquí vía JS -->
                        </tbody>
                        <tfoot>
                            <tr style="background-color: #f8f9fa; font-weight: bold;">
                                <td colspan="3" style="text-align: right; padding: 15px;">TOTAL:</td>
                                <td id="modal-total-price" style="text-align: right; padding: 15px; color: var(--gold, #D4AF37); font-size: 1.1rem;"></td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
                
                <div style="margin-top: 25px; display: flex; justify-content: flex-end;">
                    <button type="button" onclick="document.getElementById('modalDetallesPedido').style.display = 'none'" style="padding: 10px 20px; background-color: #111; color: #fff; border: none; border-radius: 4px; font-weight: 600; cursor: pointer;">Cerrar</button>
                </div>
            </div>
        </div>

    </main>

    <?php
    if (isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        echo "<script>
            Swal.fire({
                icon: '{$alert['icon']}',
                title: '{$alert['title']}',
                text: '{$alert['text']}',
                confirmButtonColor: '#D4AF37',
                background: '#111',
                color: '#fff'
            });
        </script>";
        unset($_SESSION['alert']);
    }
    ?>
    <script>
    document.querySelectorAll('.btn-ver-detalles').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.getAttribute('data-id');
            const detalles = JSON.parse(this.getAttribute('data-detalles') || '[]');
            
            document.getElementById('modal-pedido-id').innerText = '#' + id.toString().padStart(5, '0');
            
            const tbody = document.getElementById('modal-productos-list');
            tbody.innerHTML = '';
            
            let total = 0;
            
            if (detalles.length === 0) {
                tbody.innerHTML = '<tr><td colspan="4" class="text-center text-muted py-3">No hay productos en este pedido.</td></tr>';
            } else {
                detalles.forEach(item => {
                    const subtotal = item.cantidad * item.precio_unitario;
                    total += subtotal;
                    
                    const subcatText = item.subcategoria ? ' | ' + item.subcategoria : '';
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td style="padding: 12px 15px;">
                            <strong>${item.nombre}</strong><br>
                            <small class="text-muted">Ref: ${item.referencia}${subcatText}</small>
                        </td>
                        <td style="padding: 12px 15px; text-align: center;">${item.cantidad}</td>
                        <td style="padding: 12px 15px; text-align: right;">$${parseInt(item.precio_unitario).toLocaleString('es-CO')}</td>
                        <td style="padding: 12px 15px; text-align: right; font-weight: 600;">$${subtotal.toLocaleString('es-CO')}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
            
            document.getElementById('modal-total-price').innerText = '$' + total.toLocaleString('es-CO');
            document.getElementById('modalDetallesPedido').style.display = 'flex';
        });
    });

    function confirmarEliminar(id) {
        Swal.fire({
            title: '¿Eliminar Pedido?',
            text: "El pedido #" + id.toString().padStart(5, '0') + " será eliminado permanentemente de la base de datos.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar',
            background: '#111',
            color: '#fff'
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = '../../controllers/Admin/PedidoController.php?accion=eliminar&id=' + id;
            }
        })
    }
    </script>
</body>
</html>
