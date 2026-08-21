<?php
session_start();
require_once __DIR__ . '/../../models/Usuario.php';

header('Content-Type: application/json');

function responder($type, $title, $text, $redirectUrl = null) {
    echo json_encode([
        'status' => $type,
        'title' => $title,
        'message' => $text,
        'redirect' => $redirectUrl
    ]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $correo = trim($_POST['correo'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $cedula = trim($_POST['cedula'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $barrio = trim($_POST['barrio'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmar_password = $_POST['confirm_password'] ?? '';

    if (empty($nombre) || empty($apellido) || empty($correo) || empty($password)) {
        responder('error', 'Campos obligatorios', 'Nombre, Apellido, Correo y Contraseña son obligatorios.');
    }

    if ($password !== $confirmar_password) {
        responder('error', 'Contraseñas no coinciden', 'Las contraseñas ingresadas no coinciden.');
    }

    $usuarioModel = new Usuario();

    if ($usuarioModel->emailExiste($correo)) {
        responder('error', 'Correo registrado', 'El email ya está registrado.');
    }

    // 2 = Cliente
    $registrado = $usuarioModel->registrar($nombre, $apellido, $correo, $password, $telefono, $direccion, $ciudad, $barrio, $cedula, 2);

    if ($registrado) {
        responder('success', '¡Registro Exitoso!', 'Cuenta creada exitosamente. Por favor, inicia sesión.', 'login.php');
    } else {
        responder('error', 'Error de registro', 'Hubo un error al registrar. Verifica tu conexión o intenta más tarde.');
    }
} else {
    responder('error', 'Método no permitido', 'Acceso denegado.');
}
?>