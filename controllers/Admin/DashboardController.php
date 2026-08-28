<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../../views/auth/login.php");
    exit;
}

require_once __DIR__ . '/../../models/Dashboard.php';

class DashboardController
{
    private $dashboardModel;

    public function __construct()
    {
        $this->dashboardModel = new Dashboard();
    }

    public function index()
    {
        $total_productos = $this->dashboardModel->getTotalProductos();
        $total_usuarios = $this->dashboardModel->getTotalUsuarios();
        $total_pedidos = $this->dashboardModel->getTotalPedidos();
        $valor_catalogo = $this->dashboardModel->getValorCatalogo();
        
        $productos_recientes = $this->dashboardModel->getProductosRecientes(4);
        $productos_mas_vendidos = $this->dashboardModel->getProductosMasVendidos(4);

        // Al requerir la vista, las variables definidas aquí estarán disponibles
        require __DIR__ . '/../../views/admin/dashboard_admin.php';
    }
}
?>
