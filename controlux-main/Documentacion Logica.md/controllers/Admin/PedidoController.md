# Documentación: `controllers/Admin/PedidoController.php`

Este controlador permite al administrador gestionar el ciclo de vida de los pedidos (cambiando su estado de Pendiente a Procesando, Enviado, etc.).

### Explicación

#### 1. Protección de Acceso
```php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['usuario']['id_rol'] != '1') {
    header("Location: ../../views/auth/login.php");
    exit;
}
```
- Restringe de manera estricta el acceso para que ningún cliente normal (`rol 2`) pueda ejecutar acciones de modificación sobre los pedidos ajenos.

#### 2. Método `cambiarEstado()`
```php
public function cambiarEstado() {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        header("Location: ../../views/admin/pedidos.php");
        exit;
    }
    ...
    if ($id_pedido && $id_estado) {
        try {
            if ($this->pedidoModel->cambiarEstado($id_pedido, $id_estado)) {
                $_SESSION['alert'] = [
                    'icon' => 'success',
                    'title' => 'Estado Actualizado',
                    'text' => 'El pedido #' . str_pad($id_pedido, 5, '0', STR_PAD_LEFT) . ' ha sido actualizado exitosamente.'
                ];
            }
        } catch (PDOException $e) {
            ...
        }
    }
    header("Location: ../../views/admin/pedidos.php");
    exit;
}
```
- **Validación:** Verifica que la llamada sea por `POST` (proveniente del formulario oculto en la tabla de pedidos del administrador).
- **Modelo:** Llama al método `cambiarEstado()` del modelo `Pedido` pasándole el ID del pedido y el nuevo estado.
- **Formateo Visual:** Si la actualización es exitosa, guarda una variable de alerta para SweetAlert2 y utiliza `str_pad` para mostrar el ID del pedido de forma elegante rellenando con ceros (ej. `#00014` en lugar de `#14`).
- **Retorno:** Recarga la vista de pedidos para que la tabla actualice sus datos visuales en pantalla.
