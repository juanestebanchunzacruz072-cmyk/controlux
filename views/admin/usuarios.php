<?php
session_start();

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../auth/login.php");
    exit;
}

require_once '../../config/database.php';

// Paginación
$items_por_pagina = 10;
$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($pagina_actual < 1) $pagina_actual = 1;
$offset = ($pagina_actual - 1) * $items_por_pagina;

$usuarios = [];
$total_paginas = 1;

try {
    // Contar total
    $stmt_count = $conn->query("SELECT COUNT(*) FROM usuarios WHERE id_rol = '2'");
    $total_usuarios = $stmt_count->fetchColumn();
    $total_paginas = ceil($total_usuarios / $items_por_pagina);

    // Fetch users (role 2)
    $stmt = $conn->query("
        SELECT id_usuario, nombre, apellido, correo, telefono, direccion, ciudad, fecha_registro
        FROM usuarios 
        WHERE id_rol = '2'
        ORDER BY fecha_registro DESC
        LIMIT $items_por_pagina OFFSET $offset
    ");
    $usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Error al cargar los usuarios: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JC URBAN - Gestión de Usuarios</title>
    <link rel="icon" href="../../img/JC URBAN.png">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="stylesheet" href="../../public/css/style_dashboard_admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../public/css/style_productos_admin.css?v=<?php echo time(); ?>">
    <link rel="stylesheet" href="../../public/css/pagination.css?v=<?php echo time(); ?>">
</head>
<body>

    <!-- Sidebar -->
    <?php include '../layouts/sidebar_admin.php'; ?>

    <!-- Main Content -->
    <main class="main-content">
        <header class="topbar">
            <h1>LISTADO DE CLIENTES</h1>
            <div class="topbar-actions">
                <a href="dashboard_admin.php" class="btn-outline-dark-custom">
                    <i class="bi bi-arrow-left"></i> VOLVER
                </a>
            </div>
        </header>

        <section class="table-container-section mt-4">
            <div class="section-header d-flex justify-content-between align-items-center mb-3">
                <h3 class="section-title">CLIENTES REGISTRADOS</h3>
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
                            <th>PERFIL</th>
                            <th>NOMBRE COMPLETO</th>
                            <th>CORREO ELÉCTRONICO</th>
                            <th>TELÉFONO</th>
                            <th>UBICACIÓN</th>
                            <th>FECHA REGISTRO</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($usuarios)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No hay clientes registrados aún.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($usuarios as $u): ?>
                            <tr>
                                <td>
                                    <div class="user-avatar">
                                        <?php echo strtoupper(substr($u['nombre'], 0, 1) . substr($u['apellido'], 0, 1)); ?>
                                    </div>
                                </td>
                                <td>
                                    <strong><?php echo htmlspecialchars($u['nombre'] . ' ' . $u['apellido']); ?></strong>
                                </td>
                                <td><?php echo htmlspecialchars($u['correo']); ?></td>
                                <td><?php echo htmlspecialchars($u['telefono'] ?? 'No especificado'); ?></td>
                                <td>
                                    <?php 
                                        $ubicacion = array_filter([$u['ciudad'], $u['direccion']]);
                                        echo !empty($ubicacion) ? htmlspecialchars(implode(", ", $ubicacion)) : 'No especificada';
                                    ?>
                                </td>
                                <td>
                                    <span class="badge-status disponible" style="background-color: #333; color: white;">
                                        <?php echo date('d/m/Y', strtotime($u['fecha_registro'])); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <?php if (isset($total_paginas) && $total_paginas > 1): ?>
                <nav aria-label="Navegación de usuarios" class="mt-4 mb-2">
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

</body>
</html>
