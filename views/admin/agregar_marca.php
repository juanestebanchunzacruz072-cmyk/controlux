<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/database.php';

// Obtener categorías activas
$stmt = $conn->prepare("SELECT id_categoria, nombre FROM categorias WHERE activo = 1 ORDER BY nombre ASC");
$stmt->execute();
$categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener subcategorías activas
$stmt_sub = $conn->prepare("SELECT id_subcategoria, nombre, id_categoria FROM subcategoria WHERE activo = 1 ORDER BY nombre ASC");
$stmt_sub->execute();
$subcategorias = $stmt_sub->fetchAll(PDO::FETCH_ASSOC);

require_once '../../models/Marca.php';
$marcaModel = new Marca();

// Paginación y Filtro
$filtro_busqueda = trim($_GET['busqueda'] ?? '');
$filtro_categoria = $_GET['id_categoria'] ?? '';
$items_por_pagina = 10;
$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

$resultado = $marcaModel->obtenerPaginadas($filtro_categoria, $items_por_pagina, $offset, $filtro_busqueda);
$marcasList = $resultado['marcas'];
$total_paginas = ceil($resultado['total'] / $items_por_pagina);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Agregar Marca</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style_dashboard_admin.css?v=<?php echo time(); ?>">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../public/css/style_formularios_admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../public/css/style_productos_admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../public/css/pagination.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Sidebar -->
    <?php include '../layouts/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <h1>GESTIÓN DE MARCAS</h1>
            <div class="topbar-actions">
                <button type="button" class="btn-add" onclick="abrirModalAgregar()" style="background-color: var(--gold, #D4AF37); color: #111; border: none; cursor: pointer; padding: 8px 16px; font-weight: bold; border-radius: 4px; display: inline-flex; align-items: center; gap: 5px;">
                    <i class="bi bi-plus-lg"></i> AGREGAR MARCA
                </button>
            </div>
        </header>

        <section class="filters-section mt-4 mb-4" style="background: white; padding: 25px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.03); border: 1px solid var(--border, #eaeaea);">
            <h4 class="mb-3" style="font-size: 1rem; color: var(--text-gray, #6c757d); font-weight: 700; text-transform: uppercase; letter-spacing: 1px; margin-top: 0; margin-bottom: 1.5rem;"><i class="bi bi-funnel"></i> Filtrar Marcas</h4>
            <form method="GET" action="agregar_marca.php" style="display: flex; gap: 20px; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 250px;">
                    <label for="busqueda" style="display: block; font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 8px; text-transform: uppercase;">Buscar Marca</label>
                    <input type="text" id="busqueda" name="busqueda" value="<?php echo htmlspecialchars($filtro_busqueda); ?>" placeholder="Nombre de la marca..." style="width: 100%; padding: 12px 15px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; outline: none; box-sizing: border-box;">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label for="id_categoria" style="display: block; font-size: 0.8rem; font-weight: 700; color: #555; margin-bottom: 8px; text-transform: uppercase;">Por Categoría</label>
                    <select id="id_categoria" name="id_categoria" style="width: 100%; padding: 12px 15px; border-radius: 6px; border: 1px solid #ddd; font-family: 'Montserrat', sans-serif; font-size: 0.9rem; outline: none; cursor: pointer;">
                        <option value="">Todas las categorías</option>
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($filtro_categoria == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($cat['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="display: flex; gap: 10px;">
                    <button type="submit" style="padding: 12px 20px; border-radius: 6px; border: none; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; background-color: var(--gold, #D4AF37); color: #111; font-size: 0.9rem; transition: background 0.3s;"><i class="bi bi-search"></i> Filtrar</button>
                    <a href="agregar_marca.php" style="padding: 12px 20px; border-radius: 6px; border: 1px solid #ccc; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; background-color: #fff; color: #333; text-decoration: none; font-size: 0.9rem; transition: background 0.3s;"><i class="bi bi-x-circle"></i> Limpiar</a>
                </div>
            </form>
        </section>

        <!-- Modal de Agregar Marca -->
        <div id="modalAgregarMarca" class="modal" tabindex="-1" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(5px);">
            <div style="background: white; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; border-radius: 12px; padding: 30px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <button type="button" onclick="cerrarModalAgregar()" style="position: absolute; top: 20px; right: 20px; background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: #666;"><i class="bi bi-x-lg"></i></button>
                <h2 style="font-family: 'Montserrat', sans-serif; font-weight: 800; margin-bottom: 20px; font-size: 1.5rem; color: #111;">AGREGAR MARCA</h2>

                <form action="../../controllers/Admin/MarcaController.php?accion=guardar" method="POST" id="formAgregarMarca">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; color: #111;">Nombre de la Marca *</label>
                        <input type="text" name="nombre" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;" placeholder="Ej: Rolex, Cartier, Casio" required>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; color: #111;">Categorías a las que pertenece *</label>
                        <p style="font-size: 0.8rem; color: #666; margin-bottom: 10px;">Selecciona una o más categorías marcando las casillas</p>
                        <div class="checkbox-group" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; padding: 10px; background-color: #f9f9f9;">
                            <?php foreach ($categorias as $cat): ?>
                                <div class="form-check" style="margin-bottom: 5px;">
                                    <input class="form-check-input cat-checkbox" type="checkbox" name="categorias[]" value="<?php echo $cat['id_categoria']; ?>" id="cat_<?php echo $cat['id_categoria']; ?>">
                                    <label class="form-check-label" style="cursor: pointer; width: 100%; color: #333;" for="cat_<?php echo $cat['id_categoria']; ?>">
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; color: #111;">Subcategorías a las que pertenece (Opcional)</label>
                        <p style="font-size: 0.8rem; color: #666; margin-bottom: 10px;">Aparecerán aquí dependiendo de las categorías que selecciones arriba.</p>
                        <div id="contenedor_subcategorias" class="checkbox-group" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; padding: 10px; background-color: #f9f9f9;">
                            <p style="color: #666; font-size: 0.9rem; text-align: center; margin: 10px 0;">Selecciona una categoría primero...</p>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; color: #111;">Descripción</label>
                        <textarea name="descripcion" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-height: 80px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;" placeholder="Breve descripción de la marca (opcional)..."></textarea>
                    </div>

                    <button type="submit" style="width: 100%; padding: 15px; background-color: #D4AF37; color: #1a1a1a; font-weight: 800; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1rem; font-family: 'Montserrat', sans-serif; transition: 0.3s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <i class="bi bi-save"></i> GUARDAR MARCA
                    </button>
                </form>
            </div>
        </div>

        <!-- Tabla de Marcas -->
        <h3 style="font-family: 'Montserrat', sans-serif; font-weight: 630; color: #6c757d; margin-top: 1.5rem; margin-bottom: 0.5rem; text-transform: uppercase;">Lista de Marcas</h3>
        <section class="table-container-section mt-4 mb-4">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>NOMBRE DE MARCA</th>
                            <th>DESCRIPCIÓN</th>
                            <th style="text-align: center; width: 150px;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($marcasList)): ?>
                            <tr><td colspan="4" class="text-center text-muted py-4">No hay marcas registradas.</td></tr>
                        <?php else: ?>
                            <?php foreach ($marcasList as $m): ?>
                                <tr>
                                    <td>#<?php echo $m['id_marca']; ?></td>
                                    <td><strong><?php echo htmlspecialchars($m['nombre']); ?></strong></td>
                                    <td><small class="text-muted"><?php echo htmlspecialchars(substr($m['descripcion'], 0, 80)) . (strlen($m['descripcion']) > 80 ? '...' : ''); ?></small></td>
                                    <td style="text-align: center; vertical-align: middle;">
                                        <div style="display: flex; gap: 6px; justify-content: center; align-items: center;">
                                            <button type="button" class="btn btn-sm btn-editar-marca" 
                                                style="background-color: #D4AF37; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: 500;"
                                                data-id="<?php echo $m['id_marca']; ?>"
                                                data-nombre="<?php echo htmlspecialchars($m['nombre']); ?>"
                                                data-descripcion="<?php echo htmlspecialchars($m['descripcion']); ?>"
                                                data-categorias='<?php echo json_encode($m['categorias']); ?>'
                                                data-subcategorias='<?php echo json_encode($m['subcategorias']); ?>' title="Editar">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            
                                            <?php if(isset($m['activo']) && $m['activo'] == 1): ?>
                                            <a href="../../controllers/Admin/MarcaController.php?accion=ocultar&id=<?php echo $m['id_marca']; ?>" class="btn btn-sm" 
                                                style="background-color: #6c757d; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: 500; text-decoration: none;" title="Ocultar">
                                                <i class="bi bi-eye-slash"></i>
                                            </a>
                                            <?php else: ?>
                                            <a href="../../controllers/Admin/MarcaController.php?accion=activar&id=<?php echo $m['id_marca']; ?>" class="btn btn-sm" 
                                                style="background-color: #D4AF37; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: 500; text-decoration: none;" title="Mostrar">
                                                <i class="bi bi-eye"></i>
                                            </a>
                                            <?php endif; ?>
                                            
                                            <button type="button" class="btn btn-sm" onclick="confirmarEliminar(<?php echo $m['id_marca']; ?>)"
                                                style="background-color: #dc3545; color: white; border: none; padding: 6px 10px; border-radius: 4px; font-size: 0.85rem; cursor: pointer; display: inline-flex; align-items: center; justify-content: center; font-weight: 500;" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <!-- Paginación -->
        <?php if (isset($total_paginas) && $total_paginas > 1): ?>
            <nav aria-label="Navegación de marcas" class="mb-4">
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

        <!-- Modal de Edición de Marca -->
        <div id="modalEditarMarca" class="modal" tabindex="-1" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 9999; justify-content: center; align-items: center; backdrop-filter: blur(5px);">
            <div style="background: white; width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; border-radius: 12px; padding: 30px; position: relative; box-shadow: 0 10px 30px rgba(0,0,0,0.3);">
                <button type="button" onclick="cerrarModalEditar()" style="position: absolute; top: 20px; right: 20px; background: transparent; border: none; font-size: 1.5rem; cursor: pointer; color: #666;"><i class="bi bi-x-lg"></i></button>
                <h2 style="font-family: 'Montserrat', sans-serif; font-weight: 800; margin-bottom: 20px; font-size: 1.5rem; color: #111;">EDITAR MARCA</h2>
                
                <form action="../../controllers/Admin/MarcaController.php?accion=actualizar" method="POST">
                    <input type="hidden" name="id_marca" id="edit_id_marca">
                    
                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; color: #111;">Nombre de la Marca *</label>
                        <input type="text" name="nombre" id="edit_nombre" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;" required>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; color: #111;">Categorías a las que pertenece *</label>
                        <div class="checkbox-group" style="max-height: 120px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; padding: 10px; background-color: #f9f9f9;">
                            <?php foreach ($categorias as $cat): ?>
                                <div class="form-check" style="margin-bottom: 5px;">
                                    <input class="form-check-input edit-cat-checkbox" type="checkbox" name="categorias[]" value="<?php echo $cat['id_categoria']; ?>" id="edit_cat_<?php echo $cat['id_categoria']; ?>">
                                    <label class="form-check-label" style="cursor: pointer; width: 100%; color: #333;" for="edit_cat_<?php echo $cat['id_categoria']; ?>">
                                        <?php echo htmlspecialchars($cat['nombre']); ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 15px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; color: #111;">Subcategorías a las que pertenece</label>
                        <div id="edit_contenedor_subcategorias" class="checkbox-group" style="max-height: 150px; overflow-y: auto; border: 1px solid #ddd; border-radius: 6px; padding: 10px; background-color: #f9f9f9;">
                            <p style="color: #666; font-size: 0.9rem; text-align: center; margin: 10px 0;">Selecciona una categoría primero...</p>
                        </div>
                    </div>

                    <div style="margin-bottom: 25px;">
                        <label style="display: block; font-weight: 600; margin-bottom: 5px; font-size: 0.9rem; color: #111;">Descripción</label>
                        <textarea name="descripcion" id="edit_descripcion" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; min-height: 80px; font-family: 'Montserrat', sans-serif; box-sizing: border-box;"></textarea>
                    </div>

                    <button type="submit" style="width: 100%; padding: 15px; background-color: #D4AF37; color: #1a1a1a; font-weight: 800; border: none; border-radius: 6px; cursor: pointer; font-size: 1.1rem; font-family: 'Montserrat', sans-serif; transition: 0.3s;" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'">
                        <i class="bi bi-save"></i> GUARDAR CAMBIOS
                    </button>
                </form>
            </div>
        </div>
    </main>

    <script>
        const todasSubcategorias = <?php echo json_encode($subcategorias); ?>;
        
        // --- Lógica del Modal Agregar Marca ---
        const modalAgregar = document.getElementById('modalAgregarMarca');
        function abrirModalAgregar() {
            modalAgregar.style.display = 'flex';
        }
        function cerrarModalAgregar() {
            modalAgregar.style.display = 'none';
        }

        // --- Lógica del formulario de Agregar Marca ---
        const catCheckboxes = document.querySelectorAll('.cat-checkbox');
        const contenedorSubcategorias = document.getElementById('contenedor_subcategorias');

        function actualizarSubcategorias() {
            const selectedOptions = Array.from(catCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            const currentSelectedSubs = Array.from(contenedorSubcategorias.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);
            contenedorSubcategorias.innerHTML = '';
            if (selectedOptions.length === 0) {
                contenedorSubcategorias.innerHTML = '<p style="color: #999; font-size: 0.9rem; text-align: center; margin: 10px 0;">Selecciona una categoría primero...</p>';
                return;
            }
            const subsFiltradas = todasSubcategorias.filter(sub => selectedOptions.includes(sub.id_categoria.toString()));
            if (subsFiltradas.length === 0) {
                contenedorSubcategorias.innerHTML = '<p style="color: #999; font-size: 0.9rem; text-align: center; margin: 10px 0;">No hay subcategorías para esta selección.</p>';
                return;
            }
            subsFiltradas.forEach(sub => {
                const isChecked = currentSelectedSubs.includes(sub.id_subcategoria.toString()) ? 'checked' : '';
                const div = document.createElement('div');
                div.className = 'form-check';
                div.style.marginBottom = '5px';
                div.innerHTML = `
                    <input class="form-check-input" type="checkbox" name="subcategorias[]" value="${sub.id_subcategoria}" id="sub_${sub.id_subcategoria}" ${isChecked}>
                    <label class="form-check-label" style="cursor: pointer; width: 100%;" for="sub_${sub.id_subcategoria}">${sub.nombre}</label>
                `;
                contenedorSubcategorias.appendChild(div);
            });
        }
        catCheckboxes.forEach(cb => cb.addEventListener('change', actualizarSubcategorias));

        // --- Lógica del Modal de Edición ---
        const modalEditar = document.getElementById('modalEditarMarca');
        const editCatCheckboxes = document.querySelectorAll('.edit-cat-checkbox');
        const editContenedorSubcategorias = document.getElementById('edit_contenedor_subcategorias');
        let currentEditSubcategorias = [];

        function cerrarModalEditar() {
            modalEditar.style.display = 'none';
        }

        function actualizarSubcategoriasEdit() {
            const selectedOptions = Array.from(editCatCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            const userSelectedSubs = Array.from(editContenedorSubcategorias.querySelectorAll('input[type="checkbox"]:checked')).map(cb => cb.value);
            
            // Usamos las que acaba de seleccionar el usuario, o las que trajo la base de datos (si es la primera carga)
            const combinedSubs = new Set([...userSelectedSubs, ...currentEditSubcategorias]);

            editContenedorSubcategorias.innerHTML = '';
            
            if (selectedOptions.length === 0) {
                editContenedorSubcategorias.innerHTML = '<p style="color: #999; font-size: 0.9rem; text-align: center; margin: 10px 0;">Selecciona una categoría primero...</p>';
                return;
            }
            
            const subsFiltradas = todasSubcategorias.filter(sub => selectedOptions.includes(sub.id_categoria.toString()));
            if (subsFiltradas.length === 0) {
                editContenedorSubcategorias.innerHTML = '<p style="color: #999; font-size: 0.9rem; text-align: center; margin: 10px 0;">No hay subcategorías para esta selección.</p>';
                return;
            }

            subsFiltradas.forEach(sub => {
                const subStr = sub.id_subcategoria.toString();
                const isChecked = combinedSubs.has(subStr) ? 'checked' : '';
                const div = document.createElement('div');
                div.className = 'form-check';
                div.style.marginBottom = '5px';
                div.innerHTML = `
                    <input class="form-check-input" type="checkbox" name="subcategorias[]" value="${sub.id_subcategoria}" id="edit_sub_${sub.id_subcategoria}" ${isChecked}>
                    <label class="form-check-label" style="cursor: pointer; width: 100%; color: #333;" for="edit_sub_${sub.id_subcategoria}">${sub.nombre}</label>
                `;
                // Una vez renderizado con checked, lo quitamos de la lista base de DB para que el usuario pueda desmarcarlo
                if (currentEditSubcategorias.includes(subStr)) {
                    currentEditSubcategorias = currentEditSubcategorias.filter(id => id !== subStr);
                }
                editContenedorSubcategorias.appendChild(div);
            });
        }
        editCatCheckboxes.forEach(cb => cb.addEventListener('change', actualizarSubcategoriasEdit));

        // Abrir modal y cargar datos
        document.querySelectorAll('.btn-editar-marca').forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nombre = this.getAttribute('data-nombre');
                const descripcion = this.getAttribute('data-descripcion');
                const categorias = JSON.parse(this.getAttribute('data-categorias') || '[]');
                const subcategorias = JSON.parse(this.getAttribute('data-subcategorias') || '[]');

                document.getElementById('edit_id_marca').value = id;
                document.getElementById('edit_nombre').value = nombre;
                document.getElementById('edit_descripcion').value = descripcion;

                // Marcar categorías
                editCatCheckboxes.forEach(cb => {
                    cb.checked = categorias.includes(parseInt(cb.value)) || categorias.includes(cb.value);
                });

                // Preparar subcategorías
                currentEditSubcategorias = subcategorias.map(String);
                actualizarSubcategoriasEdit();

                modalEditar.style.display = 'flex';
            });
        });

        // --- Alertas ---
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('exito')) {
            Swal.fire({ title: '¡Marca Agregada!', text: 'La nueva marca ha sido guardada en el sistema.', icon: 'success', confirmButtonColor: '#D4AF37' });
            window.history.replaceState(null, null, window.location.pathname);
        } else if (urlParams.has('exito_act')) {
            Swal.fire({ title: '¡Actualizada!', text: 'La marca se actualizó correctamente.', icon: 'success', confirmButtonColor: '#D4AF37' });
            window.history.replaceState(null, null, window.location.pathname);
        } else if (urlParams.has('exito_eliminar')) {
            Swal.fire({ title: '¡Eliminada!', text: 'La marca ha sido eliminada correctamente.', icon: 'success', confirmButtonColor: '#D4AF37' });
            window.history.replaceState(null, null, window.location.pathname);
        } else if (urlParams.has('error') || urlParams.has('error_act')) {
            Swal.fire({ title: 'Error', text: 'Hubo un problema al guardar los cambios.', icon: 'error', confirmButtonColor: '#D4AF37' });
            window.history.replaceState(null, null, window.location.pathname);
        } else if (urlParams.has('error_eliminar')) {
            Swal.fire({ title: 'Error', text: 'No se pudo eliminar la marca.', icon: 'error', confirmButtonColor: '#dc3545' });
            window.history.replaceState(null, null, window.location.pathname);
        }
    </script>
</body>
</html>
