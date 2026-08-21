<?php
session_start();
// Para hacer este botón completamente funcional necesitas:
// 1. Ir a Google Cloud Console (https://console.cloud.google.com/)
// 2. Crear un proyecto y configurar la Pantalla de Consentimiento de OAuth
// 3. Crear Credenciales (ID de cliente de OAuth 2.0)
// 4. Instalar la librería de Google (composer require google/apiclient)

$_SESSION['alert'] = [
    'icon' => 'info',
    'title' => 'Integración de Google',
    'text' => 'El diseño del botón está listo. Para procesar inicios de sesión reales, debes enlazar tus credenciales de Google Cloud Console.'
];

header('Location: ../../views/auth/login.php');
exit();
?>
