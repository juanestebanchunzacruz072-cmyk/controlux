<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/database.php';

// Filtros GET
$filtro_categoria = $_GET['id_categoria'] ?? '';
$filtro_subcategoria = $_GET['id_subcategoria'] ?? '';
$filtro_marca = $_GET['id_marca'] ?? '';

// Fetch options & products
$productos = [];
$categorias_lista = [];
$subcategorias_lista = [];
$marcas_lista = [];

try {
    // Obtener listas para los filtros
    $stmt_cat = $conn->query("SELECT id_categoria, nombre FROM categorias WHERE activo = 1 ORDER BY nombre ASC");
    $categorias_lista = $stmt_cat->fetchAll(PDO::FETCH_ASSOC);

    $stmt_sub = $conn->query("SELECT id_subcategoria, nombre, id_categoria FROM subcategoria WHERE activo = 1 ORDER BY nombre ASC");
    $subcategorias_lista = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);

    $stmt_mar = $conn->query("SELECT id_marca, nombre FROM marcas WHERE activo = 1 ORDER BY nombre ASC");
    $marcas_lista = $stmt_mar->fetchAll(PDO::FETCH_ASSOC);

    $stmt_mc = $conn->query("SELECT id_marca, id_categoria FROM marcas_categorias");
    $marcas_categorias_lista = $stmt_mc->fetchAll(PDO::FETCH_ASSOC);
    $marcas_cat_lista = $conn->query("SELECT DISTINCT id_marca, id_categoria FROM productos WHERE id_marca IS NOT NULL AND id_categoria IS NOT NULL")->fetchAll(PDO::FETCH_ASSOC);

    // Variables de paginación
    $items_por_pagina = 10;
    $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    if ($pagina_actual < 1) $pagina_actual = 1;
    $offset = ($pagina_actual - 1) * $items_por_pagina;

    // Base query para contar
    $sql_count = "SELECT COUNT(*) FROM productos p WHERE 1=1";
    
    $sql_productos = "
        SELECT p.*, c.nombre as categoria, s.nombre as subcategoria, m.nombre as marca 
        FROM productos p 
        LEFT JOIN categorias c ON p.id_categoria = c.id_categoria 
        LEFT JOIN subcategoria s ON p.id_subcategoria = s.id_subcategoria
        LEFT JOIN marcas m ON p.id_marca = m.id_marca
        WHERE 1=1
    ";
    
    $params = [];
    if (!empty($filtro_categoria)) {
        $sql_productos .= " AND p.id_categoria = :id_categoria";
        $sql_count .= " AND p.id_categoria = :id_categoria";
        $params[':id_categoria'] = $filtro_categoria;
    }
    if (!empty($filtro_subcategoria)) {
        $sql_productos .= " AND p.id_subcategoria = :id_subcategoria";
        $sql_count .= " AND p.id_subcategoria = :id_subcategoria";
        $params[':id_subcategoria'] = $filtro_subcategoria;
    }
    if (!empty($filtro_marca)) {
        $sql_productos .= " AND p.id_marca = :id_marca";
        $sql_count .= " AND p.id_marca = :id_marca";
        $params[':id_marca'] = $filtro_marca;
    }
    
    // Ejecutar conteo
    $stmt_count = $conn->prepare($sql_count);
    $stmt_count->execute($params);
    $total_productos = $stmt_count->fetchColumn();
    $total_paginas = ceil($total_productos / $items_por_pagina);

    // Añadir orden y paginación a la consulta principal
    $sql_productos .= " ORDER BY p.id_producto DESC LIMIT $items_por_pagina OFFSET $offset";

    $stmt = $conn->prepare($sql_productos);
    $stmt->execute($params);
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    // Manejo de error si falla
    $error = "Error al cargar los datos: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Gestión de Productos</title>
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
        <header class="topbar">
            <h1>INVENTARIO DE PRODUCTOS</h1>
            <div class="topbar-actions">
                <a href="../../public/index.php" class="btn-outline-dark-custom" target="_blank" title="Abrir tienda en una nueva pestaña">
                    <i class="bi bi-shop"></i> VER TIENDA
                </a>
            </div>
        </header>

        <section class="filters-section mt-4 mb-4" style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border, #eaeaea); margin-bottom: 2rem;">
            <h4 class="mb-3" style="font-size: 1rem; color: var(--text-gray, #6c757d); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-top: 0; margin-bottom: 1.5rem;"><i class="bi bi-funnel"></i> Filtros de Búsqueda</h4>
            <form method="GET" action="productos.php" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label for="id_categoria" style="display: block; font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Categoría</label>
                    <select id="id_categoria" name="id_categoria" style="width: 100%; padding: 12px 15px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; color: #333; background-color: #f9f9f9; outline: none; cursor: pointer;; box-sizing: border-box;">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias_lista as $cat): ?>
                            <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($filtro_categoria == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label for="id_subcategoria" style="display: block; font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Subcategoría</label>
                    <select id="id_subcategoria" name="id_subcategoria" style="width: 100%; padding: 12px 15px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; color: #333; background-color: #f9f9f9; outline: none; cursor: pointer;; box-sizing: border-box;">
                        <option value="">Todas las subcategorías</option>
                        <?php foreach ($subcategorias_lista as $sub): ?>
                            <option value="<?php echo $sub['id_subcategoria']; ?>" data-categoria="<?php echo $sub['id_categoria']; ?>" <?php echo ($filtro_subcategoria == $sub['id_subcategoria']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sub['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label for="id_marca" style="display: block; font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 0.5px;">Marca</label>
                    <select id="id_marca" name="id_marca" style="width: 100%; padding: 12px 15px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; color: #333; background-color: #f9f9f9; outline: none; cursor: pointer;; box-sizing: border-box;">
                        <option value="">Todas las marcas</option>
                        <?php foreach ($marcas_lista as $mar): ?>
                            <option value="<?php echo $mar['id_marca']; ?>" <?php echo ($filtro_marca == $mar['id_marca']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mar['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 10px; flex: 1; min-width: 250px;">
                    <button type="submit" style="flex: 1; padding: 12px 15px; border-radius: 6px; border: none; font-weight: 700; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; font-family: 'Montserrat', sans-serif; background-color: var(--gold, #D4AF37); color: #111; font-size: 0.9rem; transition: background 0.3s; text-transform: uppercase;"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="productos.php" style="flex: 1; padding: 12px 15px; border-radius: 6px; border: 1px solid #ccc; font-weight: 700; cursor: pointer; display: flex; justify-content: center; align-items: center; gap: 8px; font-family: 'Montserrat', sans-serif; background-color: #fff; color: #333; text-decoration: none; font-size: 0.9rem; transition: background 0.3s; text-transform: uppercase;"><i class="bi bi-x-circle"></i> Limpiar</a>
                </div>
            </form>
        </section>

        <section class="table-container-section mt-4">
            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title">LISTADO COMPLETO</h3>
                <button type="button" class="btn-add" onclick="abrirModalProducto()" style="background-color: var(--gold, #D4AF37); color: #111; border: none; cursor: pointer; padding: 8px 16px; font-weight: bold; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bi bi-plus"></i> AGREGAR PRODUCTO
                </button>
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
                            <th>ID</th>
                            <th>IMAGEN</th>
                            <th>NOMBRE / REFERENCIA</th>
                            <th>CATEGORÍA</th>
                            <th>PRECIO</th>
                            <th>STOCK</th>
                            <th>ESTADO</th>
                            <th style="text-align: center; width: 150px;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($productos)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No hay productos en el inventario.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($productos as $p): ?>
                            <tr>
                                <td>#<?php echo $p['id_producto']; ?></td>
                                <td>
                                    <?php if (!empty($p['img'])): ?>
                                        <img src="../../<?php echo htmlspecialchars($p['img']); ?>" class="table-img" style="width: 50px; height: 50px; object-fit: contain;">
                                    <?php else: ?>
                                        <div class="table-img d-flex align-items-center justify-content-center bg-light" style="width: 50px; height: 50px;">
                                            <i class="bi bi-image text-muted"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($p['nombre'] ?? ''); ?></strong><br>
                                    <small class="text-muted">Ref: <?php echo htmlspecialchars($p['referencia'] ?? 'N/A'); ?></small>
                                </td>
                                <td>
                                    <?php 
                                    $cat_text = htmlspecialchars($p['categoria'] ?? '');
                                    if (!empty($p['subcategoria'])) {
                                        $cat_text .= ' &gt; ' . htmlspecialchars($p['subcategoria']);
                                    }
                                    if (!empty($p['genero']) && $p['genero'] !== 'Unisex') {
                                        $cat_text .= ' (' . htmlspecialchars($p['genero']) . ')';
                                    }
                                    echo $cat_text;
                                    ?><br>
                                    <small class="text-muted"><?php echo htmlspecialchars($p['marca'] ?? ''); ?></small>
                                </td>
                                <td class="price-col">$ <?php echo number_format($p['precio'] ?? 0, 0, ',', '.'); ?></td>
                                <td>
                                    <?php echo $p['stock']; ?>
                                </td>
                                <td>
                                    <?php if ($p['activo'] == 1): ?>
                                        <span class="badge-status disponible">ACTIVO</span>
                                    <?php else: ?>
                                        <span class="badge-status" style="background-color: #e2e3e5; color: #383d41;">INACTIVO</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center; vertical-align: middle;">
                                    <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                        <button type="button" onclick='abrirModalEditarProducto(<?php echo htmlspecialchars(json_encode($p), ENT_QUOTES, "UTF-8"); ?>)' 
                                            style="background-color: #D4AF37; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: 500; text-decoration: none;" title="Editar">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>
                                        
                                        <form action="../../controllers/Admin/ProductoController.php?accion=toggleStatus" method="POST" class="d-inline" style="margin: 0;" <?php if ($p['activo'] == 1) echo 'onsubmit="confirmarDesactivacion(event, this)"'; ?>>
                                            <input type="hidden" name="id_producto" value="<?php echo $p['id_producto']; ?>">
                                            <input type="hidden" name="estado_actual" value="<?php echo $p['activo']; ?>">
                                            <?php if ($p['activo'] == 1): ?>
                                                <button type="submit" 
                                                    style="background-color: #6c757d; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: 500;" title="Ocultar">
                                                    <i class="bi bi-eye-slash"></i>
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" 
                                                    style="background-color: #D4AF37; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: 500;" title="Activar">
                                                    <i class="bi bi-eye"></i>
                                                </button>
                                            <?php endif; ?>
                                        </form>
                                        
                                        <form action="../../controllers/Admin/ProductoController.php?accion=eliminar" method="POST" class="d-inline" onsubmit="confirmarEliminacion(event, this)" style="margin: 0;">
                                            <input type="hidden" name="id_producto" value="<?php echo $p['id_producto']; ?>">
                                            <button type="submit" 
                                                style="background-color: #dc3545; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: 500;" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
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
                <nav aria-label="Navegación de productos">
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
        const marcasCategorias = <?php echo json_encode($marcas_cat_lista); ?>;
        document.addEventListener('DOMContentLoaded', function() {
            const catSelect = document.getElementById('id_categoria');
            const subSelect = document.getElementById('id_subcategoria');
            const marcaSelect = document.getElementById('id_marca');

            const allSubOptions = Array.from(subSelect.querySelectorAll('option[data-categoria]'));
            const allMarcaOptions = Array.from(marcaSelect.querySelectorAll('option[value]:not([value=""])'));

            function filterOptions() {
                const catId = catSelect.value;
                
                allSubOptions.forEach(opt => {
                    if (!catId || opt.dataset.categoria === catId) {
                        opt.style.display = '';
                        opt.hidden = false;
                        opt.disabled = false;
                    } else {
                        opt.style.display = 'none';
                        opt.hidden = true;
                        opt.disabled = true;
                    }
                });

                allMarcaOptions.forEach(opt => {
                    if (!catId) {
                        opt.style.display = '';
                        opt.hidden = false;
                        opt.disabled = false;
                    } else {
                        const marcaId = opt.value;
                        const hasCategory = marcasCategorias.some(mc => mc.id_marca == marcaId && mc.id_categoria == catId);
                        if (hasCategory) {
                            opt.style.display = '';
                            opt.hidden = false;
                            opt.disabled = false;
                        } else {
                            opt.style.display = 'none';
                            opt.hidden = true;
                            opt.disabled = true;
                        }
                    }
                });

                if (subSelect.options[subSelect.selectedIndex] && subSelect.options[subSelect.selectedIndex].disabled) {
                    subSelect.value = '';
                }
                if (marcaSelect.options[marcaSelect.selectedIndex] && marcaSelect.options[marcaSelect.selectedIndex].disabled) {
                    marcaSelect.value = '';
                }
            }

            catSelect.addEventListener('change', function() {
                filterOptions();
                document.getElementById('filterForm').submit();
            });
            
            subSelect.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
            
            marcaSelect.addEventListener('change', function() {
                document.getElementById('filterForm').submit();
            });
            filterOptions();
        });

        // Alertas de confirmación SweetAlert2
        function confirmarDesactivacion(event, form) {
            event.preventDefault();
            Swal.fire({
                title: '¿Desactivar producto?',
                text: '¿Seguro que deseas desactivar este producto?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#D4AF37',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, desactivar',
                cancelButtonText: 'Cancelar',
                background: '#111',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }

        function confirmarEliminacion(event, form) {
            event.preventDefault();
            Swal.fire({
                title: '¿Eliminar producto?',
                text: '¿Seguro que deseas eliminar completamente este producto? ¡Esta acción no se puede deshacer!',
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                background: '#111',
                color: '#fff'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        }
    </script>

    <!-- Modal Agregar Producto -->
    <div id="modalAgregarProducto" class="modal-overlay">
        <div class="modal-custom">
            <button type="button" onclick="cerrarModalProducto()" class="close-btn"><i class="bi bi-x-lg"></i></button>
            <h2 style="font-family: 'Montserrat', sans-serif; font-weight: 800; margin-bottom: 20px; font-size: 1.5rem;">AGREGAR PRODUCTO</h2>
            
            <form action="../../controllers/Admin/ProductoController.php?accion=guardar" method="POST" enctype="multipart/form-data">
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Nombre del Producto *</label>
                    <input type="text" name="nombre" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif;; box-sizing: border-box;" placeholder="Ej: Rolex Submariner Date" required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Referencia / SKU *</label>
                    <input type="text" name="referencia" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif;; box-sizing: border-box;" placeholder="Ej: REF-12345" required>
                </div>

                <div class="modal-form-grid">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Categoría *</label>
                        <select name="id_categoria" id="modal_id_categoria" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif;; box-sizing: border-box;" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias_lista as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Subcategoría</label>
                        <select name="id_subcategoria" id="modal_id_subcategoria" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif;; box-sizing: border-box;">
                            <option value="">Seleccione primero una categoría</option>
                        </select>
                    </div>
                </div>

                <div class="modal-form-grid">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <label style="font-weight: 600; margin: 0; font-size: 0.9rem;">Marca *</label>
                            <label style="font-size: 0.8rem; cursor: pointer; color: #666; font-weight: normal; margin: 0;">
                                <input type="checkbox" id="modal_mostrar_todas_marcas"> Mostrar todas
                            </label>
                        </div>
                        <select name="id_marca" id="modal_id_marca" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif;; box-sizing: border-box;" required>
                            <option value="">Seleccione una marca</option>
                            <?php foreach ($marcas_lista as $marca): ?>
                                <option value="<?php echo $marca['id_marca']; ?>"><?php echo htmlspecialchars($marca['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Género *</label>
                        <select name="genero" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif;; box-sizing: border-box;" required>
                            <option value="Unisex">Unisex (Ambos)</option>
                            <option value="Hombre">Hombre</option>
                            <option value="Mujer">Mujer</option>
                        </select>
                    </div>
                </div>

                <div class="modal-form-grid">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Precio (COP) *</label>
                        <input type="number" name="precio" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; outline: none;; box-sizing: border-box;" placeholder="Ej: 4500000" min="0" required>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Stock *</label>
                        <input type="number" name="stock" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; outline: none;; box-sizing: border-box;" placeholder="Ej: 5" min="0" required>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Descripción</label>
                    <textarea name="descripcion" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-height: 80px; font-family: 'Montserrat', sans-serif;; box-sizing: border-box;" placeholder="Escribe los detalles del producto..."></textarea>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Imagen Principal *</label>
                    <input type="file" name="imagen_principal" accept="image/png, image/jpeg, image/webp" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9;; box-sizing: border-box;" required>
                </div>

                <button type="submit" style="width: 100%; padding: 15px; background-color: #D4AF37; color: #1a1a1a; font-weight: 800; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1rem; font-family: 'Montserrat', sans-serif; transition: 0.3s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    <i class="bi bi-save"></i> GUARDAR PRODUCTO
                </button>
            </form>
        </div>
    </div>

    <script>
        const marcasCategoriasModal = <?php echo json_encode($marcas_categorias_lista); ?>;
        
        function abrirModalProducto() {
            document.getElementById('modalAgregarProducto').style.display = 'flex';
        }
        function cerrarModalProducto() {
            document.getElementById('modalAgregarProducto').style.display = 'none';
        }

        // Lógica de Subcategorías Dinámicas (Modal)
        document.getElementById('modal_id_categoria').addEventListener('change', function() {
            const idCategoria = this.value;
            const subcategoriaSelect = document.getElementById('modal_id_subcategoria');
            
            subcategoriaSelect.innerHTML = '<option value="">Cargando subcategorías...</option>';
            subcategoriaSelect.disabled = true;

            if (idCategoria) {
                fetch(`../../controllers/Admin/SubcategoriaController.php?accion=obtenerPorCategoria&id_categoria=${idCategoria}`)
                    .then(response => response.json())
                    .then(data => {
                        subcategoriaSelect.disabled = false;
                        subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría (Opcional)</option>';
                        
                        if (data.length > 0) {
                            data.forEach(sub => {
                                const option = document.createElement('option');
                                option.value = sub.id_subcategoria;
                                option.textContent = sub.nombre;
                                subcategoriaSelect.appendChild(option);
                            });
                        } else {
                            subcategoriaSelect.innerHTML = '<option value="">Sin subcategorías</option>';
                            subcategoriaSelect.disabled = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        subcategoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
                    });
            } else {
                subcategoriaSelect.innerHTML = '<option value="">Seleccione primero una categoría</option>';
                subcategoriaSelect.disabled = true;
            }
            filtrarMarcasModal();
        });

        // Lógica de filtrado de Marcas (Modal)
        const marcaSelectModal = document.getElementById('modal_id_marca');
        const allMarcaOptionsModal = Array.from(marcaSelectModal.querySelectorAll('option[value]:not([value=""])'));
        const checkboxMostrarTodasModal = document.getElementById('modal_mostrar_todas_marcas');

        function filtrarMarcasModal() {
            const idCategoria = document.getElementById('modal_id_categoria').value;
            const mostrarTodas = checkboxMostrarTodasModal.checked;

            allMarcaOptionsModal.forEach(opt => {
                if (mostrarTodas || !idCategoria) {
                    opt.style.display = '';
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    const marcaId = opt.value;
                    const hasCategory = marcasCategoriasModal.some(mc => mc.id_marca == marcaId && mc.id_categoria == idCategoria);
                    if (hasCategory) {
                        opt.style.display = '';
                        opt.hidden = false;
                        opt.disabled = false;
                    } else {
                        opt.style.display = 'none';
                        opt.hidden = true;
                        opt.disabled = true;
                    }
                }
            });

            if (marcaSelectModal.options[marcaSelectModal.selectedIndex] && marcaSelectModal.options[marcaSelectModal.selectedIndex].disabled) {
                marcaSelectModal.value = '';
            }
        }

        checkboxMostrarTodasModal.addEventListener('change', filtrarMarcasModal);
    </script>

    <!-- Modal Editar Producto -->
    <div id="modalEditarProducto" class="modal-overlay">
        <div class="modal-custom">
            <button type="button" onclick="cerrarModalEditarProducto()" class="close-btn"><i class="bi bi-x-lg"></i></button>
            <h2 style="font-family: 'Montserrat', sans-serif; font-weight: 800; margin-bottom: 20px; font-size: 1.5rem;">EDITAR PRODUCTO</h2>
            
            <form action="../../controllers/Admin/ProductoController.php?accion=editar" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="id_producto" id="edit_id_producto">
                
                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Nombre del Producto *</label>
                    <input type="text" name="nombre" id="edit_nombre" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;" required>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Referencia / SKU *</label>
                    <input type="text" name="referencia" id="edit_referencia" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;" required>
                </div>

                <div class="modal-form-grid">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Categoría *</label>
                        <select name="id_categoria" id="edit_id_categoria" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias_lista as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>"><?php echo htmlspecialchars($cat['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Subcategoría</label>
                        <select name="id_subcategoria" id="edit_id_subcategoria" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;">
                            <option value="">Seleccione primero una categoría</option>
                        </select>
                    </div>
                </div>

                <div class="modal-form-grid">
                    <div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
                            <label style="font-weight: 600; margin: 0; font-size: 0.9rem;">Marca *</label>
                            <label style="font-size: 0.8rem; cursor: pointer; color: #666; font-weight: normal; margin: 0;">
                                <input type="checkbox" id="edit_mostrar_todas_marcas"> Mostrar todas
                            </label>
                        </div>
                        <select name="id_marca" id="edit_id_marca" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;" required>
                            <option value="">Seleccione una marca</option>
                            <?php foreach ($marcas_lista as $marca): ?>
                                <option value="<?php echo $marca['id_marca']; ?>"><?php echo htmlspecialchars($marca['nombre']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Género *</label>
                        <select name="genero" id="edit_genero" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;" required>
                            <option value="Unisex">Unisex (Ambos)</option>
                            <option value="Hombre">Hombre</option>
                            <option value="Mujer">Mujer</option>
                        </select>
                    </div>
                </div>

                <div class="modal-form-grid">
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Precio (COP) *</label>
                        <input type="number" name="precio" id="edit_precio" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; outline: none; box-sizing: border-box;" min="0" required>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Stock *</label>
                        <input type="number" name="stock" id="edit_stock" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; outline: none; box-sizing: border-box;" min="0" required>
                    </div>
                </div>

                <div style="margin-bottom: 15px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Descripción</label>
                    <textarea name="descripcion" id="edit_descripcion" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-height: 80px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;"></textarea>
                </div>

                <div style="margin-bottom: 25px;">
                    <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem;">Imagen Principal (Dejar vacío para no cambiar)</label>
                    <input type="file" name="imagen_principal" accept="image/png, image/jpeg, image/webp" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; background: #f9f9f9; box-sizing: border-box;">
                </div>

                <button type="submit" style="width: 100%; padding: 15px; background-color: #D4AF37; color: #1a1a1a; font-weight: 800; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1rem; font-family: 'Montserrat', sans-serif; transition: 0.3s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                    <i class="bi bi-save"></i> ACTUALIZAR PRODUCTO
                </button>
            </form>
        </div>
    </div>

    <script>
        function cerrarModalEditarProducto() {
            document.getElementById('modalEditarProducto').style.display = 'none';
        }

        function abrirModalEditarProducto(p) {
            document.getElementById('edit_id_producto').value = p.id_producto;
            document.getElementById('edit_nombre').value = p.nombre || '';
            document.getElementById('edit_referencia').value = p.referencia || '';
            document.getElementById('edit_genero').value = p.genero || 'Unisex';
            document.getElementById('edit_precio').value = p.precio || 0;
            document.getElementById('edit_stock').value = p.stock || 0;
            document.getElementById('edit_descripcion').value = p.descripcion || '';
            
            document.getElementById('edit_id_categoria').value = p.id_categoria || '';
            
            cargarSubcategoriasYMarcasEditar(p.id_categoria, p.id_subcategoria, p.id_marca);
            
            document.getElementById('modalEditarProducto').style.display = 'flex';
        }

        function cargarSubcategoriasYMarcasEditar(idCategoria, selectedSub, selectedMarca) {
            const subcategoriaSelect = document.getElementById('edit_id_subcategoria');
            subcategoriaSelect.innerHTML = '<option value="">Cargando subcategorías...</option>';
            subcategoriaSelect.disabled = true;

            if (idCategoria) {
                fetch(`../../controllers/Admin/SubcategoriaController.php?accion=obtenerPorCategoria&id_categoria=${idCategoria}`)
                    .then(response => response.json())
                    .then(data => {
                        subcategoriaSelect.disabled = false;
                        subcategoriaSelect.innerHTML = '<option value="">Seleccione una subcategoría (Opcional)</option>';
                        
                        if (data.length > 0) {
                            data.forEach(sub => {
                                const option = document.createElement('option');
                                option.value = sub.id_subcategoria;
                                option.textContent = sub.nombre;
                                if(sub.id_subcategoria == selectedSub) option.selected = true;
                                subcategoriaSelect.appendChild(option);
                            });
                        } else {
                            subcategoriaSelect.innerHTML = '<option value="">Sin subcategorías</option>';
                            subcategoriaSelect.disabled = true;
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        subcategoriaSelect.innerHTML = '<option value="">Error al cargar</option>';
                    });
            } else {
                subcategoriaSelect.innerHTML = '<option value="">Seleccione primero una categoría</option>';
                subcategoriaSelect.disabled = true;
            }
            
            filtrarMarcasEditar();
            if(selectedMarca) {
                setTimeout(() => { document.getElementById('edit_id_marca').value = selectedMarca; }, 100);
            }
        }

        const marcaSelectEditar = document.getElementById('edit_id_marca');
        const allMarcaOptionsEditar = Array.from(marcaSelectEditar.querySelectorAll('option[value]:not([value=""])'));
        const checkboxMostrarTodasEditar = document.getElementById('edit_mostrar_todas_marcas');

        function filtrarMarcasEditar() {
            const idCategoria = document.getElementById('edit_id_categoria').value;
            const mostrarTodas = checkboxMostrarTodasEditar.checked;

            allMarcaOptionsEditar.forEach(opt => {
                if (mostrarTodas || !idCategoria) {
                    opt.style.display = '';
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    const marcaId = opt.value;
                    const hasCategory = marcasCategoriasModal.some(mc => mc.id_marca == marcaId && mc.id_categoria == idCategoria);
                    if (hasCategory) {
                        opt.style.display = '';
                        opt.hidden = false;
                        opt.disabled = false;
                    } else {
                        opt.style.display = 'none';
                        opt.hidden = true;
                        opt.disabled = true;
                    }
                }
            });

            if (marcaSelectEditar.options[marcaSelectEditar.selectedIndex] && marcaSelectEditar.options[marcaSelectEditar.selectedIndex].disabled) {
                marcaSelectEditar.value = '';
            }
        }

        document.getElementById('edit_id_categoria').addEventListener('change', function() {
            cargarSubcategoriasYMarcasEditar(this.value, null, null);
        });
        checkboxMostrarTodasEditar.addEventListener('change', filtrarMarcasEditar);

    </script>
</body>
</html>
