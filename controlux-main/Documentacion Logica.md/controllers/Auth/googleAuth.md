# Documentación: `controllers/Auth/googleAuth.php`

Este archivo es un controlador temporal para el botón de inicio de sesión con Google.

### Explicación

#### 1. Mensaje de Alerta (Simulación)
```php
<?php
session_start();
// Para hacer este botón completamente funcional necesitas:
// 1. Ir a Google Cloud Console...

$_SESSION['alert'] = [
    'icon' => 'info',
    'title' => 'Integración de Google',
    'text' => 'El diseño del botón está listo. Para procesar inicios de sesión reales, debes enlazar tus credenciales de Google Cloud Console.'
];

header('Location: ../../views/auth/login.php');
exit();
?>
```
- **Propósito actual:** Como la tienda aún no tiene enlazado un `ID de Cliente OAuth 2.0` real desde Google Cloud Console, este archivo simplemente intercepta el click del usuario en el botón de Google.
- Genera un `SweetAlert` informativo indicando qué pasos faltan a nivel de servidor (instalar librería de Google PHP y configurar credenciales).
- **Redirección:** Devuelve al usuario a la página de login para que pueda continuar usando el inicio de sesión manual mientras tanto.
