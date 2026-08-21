<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../auth/login.php");
    exit;
}

// Las variables $categorias, $marcas, $marcas_cat y $producto
// son inyectadas por ProductoController.php
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Agregar Producto</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style_dashboard_admin.css?v=<?php echo time(); ?>">
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="../../public/css/style_formularios_admin.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Sidebar -->
    <?php include '../layouts/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar">
            <h1>EDITAR PRODUCTO #<?php echo $producto['id_producto']; ?></h1>
            <div class="topbar-actions">
                <a href="productos.php" class="btn-outline-dark-custom">
                    <i class="bi bi-arrow-left"></i> VOLVER
                </a>
            </div>
        </header>

        <section class="form-container">
            <form action="../../controllers/Admin/ProductoController.php?accion=editar" method="POST" enctype="multipart/form-data" id="formAgregarProducto">
                <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                
                <div class="form-group">
                    <label class="form-label">Nombre del Producto *</label>
                    <input type="text" name="nombre" class="form-control" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label class="form-label">Referencia / SKU *</label>
                    <input type="text" name="referencia" class="form-control" value="<?php echo htmlspecialchars($producto['referencia'] ?? ''); ?>" required>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Categoría *</label>
                        <select name="id_categoria" id="id_categoria" class="form-select" required>
                            <option value="">Seleccione una categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id_categoria']; ?>" <?php echo ($producto['id_categoria'] == $cat['id_categoria']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Subcategoría</label>
                        <select name="id_subcategoria" id="id_subcategoria" class="form-select" data-selected="<?php echo $producto['id_subcategoria']; ?>">
                            <option value="">Seleccione primero una categoría</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                            <label class="form-label" style="margin-bottom: 0;">Marca *</label>
                            <label style="font-size: 0.8rem; cursor: pointer; color: #666; font-weight: normal; margin-bottom: 0;">
                                <input type="checkbox" id="mostrar_todas_marcas"> Mostrar todas
                            </label>
                        </div>
                        <select name="id_marca" id="id_marca" class="form-select" required>
                            <option value="">Seleccione una marca</option>
                            <?php foreach ($marcas as $marca): ?>
                                <option value="<?php echo $marca['id_marca']; ?>" <?php echo ($producto['id_marca'] == $marca['id_marca']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($marca['nombre']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Género *</label>
                        <select name="genero" class="form-select" required>
                            <option value="Unisex" <?php echo ($producto['genero'] == 'Unisex') ? 'selected' : ''; ?>>Unisex (Ambos)</option>
                            <option value="Hombre" <?php echo ($producto['genero'] == 'Hombre') ? 'selected' : ''; ?>>Hombre</option>
                            <option value="Mujer" <?php echo ($producto['genero'] == 'Mujer') ? 'selected' : ''; ?>>Mujer</option>
                        </select>
                    </div>
                </div>

                <div class="grid-2">
                    <div class="form-group">
                        <label class="form-label">Precio (COP) *</label>
                        <input type="number" name="precio" class="form-control" value="<?php echo $producto['precio']; ?>" min="0" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Stock *</label>
                        <input type="number" name="stock" class="form-control" value="<?php echo $producto['stock']; ?>" min="0" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Descripción</label>
                    <textarea name="descripcion" class="form-control"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Imagen Principal (Dejar vacío para no cambiar)</label>
                    <div class="file-upload-wrapper">
                        <i class="bi bi-cloud-arrow-up file-upload-icon"></i>
                        <div class="file-upload-text" id="fileNameText">Selecciona una imagen nueva o mantén la actual</div>
                        <input type="file" name="imagen_principal" id="imagen_principal" accept="image/png, image/jpeg, image/webp">
                    </div>
                </div>

                <button type="submit" class="btn-submit">
                    <i class="bi bi-save"></i> ACTUALIZAR PRODUCTO
                </button>
            </form>
        </section>
    </main>

    <script>
        // Lógica de Subcategorías Dinámicas
        function loadSubcategories(idCategoria, selectedSub = null) {
            const subcategoriaSelect = document.getElementById('id_subcategoria');
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
                                if (selectedSub && selectedSub == sub.id_subcategoria) {
                                    option.selected = true;
                                }
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

            filtrarMarcas();
        }

        // Lógica de filtrado de Marcas
        const marcasCategorias = <?php echo json_encode($marcas_cat); ?>;
        const marcaSelect = document.getElementById('id_marca');
        const allMarcaOptions = Array.from(marcaSelect.querySelectorAll('option[value]:not([value=""])'));
        const checkboxMostrarTodas = document.getElementById('mostrar_todas_marcas');

        function filtrarMarcas() {
            const idCategoria = document.getElementById('id_categoria').value;
            const mostrarTodas = checkboxMostrarTodas.checked;

            allMarcaOptions.forEach(opt => {
                if (mostrarTodas || !idCategoria) {
                    opt.style.display = '';
                    opt.hidden = false;
                    opt.disabled = false;
                } else {
                    const marcaId = opt.value;
                    const hasCategory = marcasCategorias.some(mc => mc.id_marca == marcaId && mc.id_categoria == idCategoria);
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

            if (marcaSelect.options[marcaSelect.selectedIndex] && marcaSelect.options[marcaSelect.selectedIndex].disabled) {
                marcaSelect.value = '';
            }
        }

        checkboxMostrarTodas.addEventListener('change', filtrarMarcas);
        
        // Ejecutar filtros al cargar por si ya hay una categoría seleccionada (modo edición)
        document.addEventListener('DOMContentLoaded', function() {
            filtrarMarcas();
        });

        // Event listener for changes
        document.getElementById('id_categoria').addEventListener('change', function() {
            loadSubcategories(this.value);
        });

        // Initialize on page load
        window.addEventListener('DOMContentLoaded', () => {
            const catSelect = document.getElementById('id_categoria');
            const subSelect = document.getElementById('id_subcategoria');
            if(catSelect.value) {
                loadSubcategories(catSelect.value, subSelect.dataset.selected);
            }
        });

        // Actualizar el texto cuando se selecciona un archivo
        document.getElementById('imagen_principal').addEventListener('change', function(e) {
            const fileName = e.target.files[0] ? e.target.files[0].name : 'Haz clic o arrastra una imagen aquí (.jpg, .png, .webp)';
            document.getElementById('fileNameText').innerText = fileName;
        });

        // Mostrar SweetAlert si hay un parámetro 'exito' en la URL
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('exito')) {
            Swal.fire({
                title: '¡Producto Agregado!',
                text: 'El producto se ha guardado correctamente en el catálogo.',
                icon: 'success',
                confirmButtonColor: '#D4AF37',
                confirmButtonText: 'Aceptar'
            });
            // Limpiar la URL
            window.history.replaceState(null, null, window.location.pathname);
        } else if (urlParams.has('error')) {
            Swal.fire({
                title: 'Error',
                text: 'Hubo un problema al guardar el producto. Inténtalo de nuevo.',
                icon: 'error',
                confirmButtonColor: '#D4AF37'
            });
        }
    </script>
</body>
</html>
