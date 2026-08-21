<?php
session_start();
require_once '../../config/database.php';

// Validar que el usuario esté autenticado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../../views/auth/login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];
    
    // Solo permitimos actualizar dirección y ciudad
    $direccion = trim($_POST['direccion'] ?? '');
    $ciudad = trim($_POST['ciudad'] ?? '');
    $barrio = trim($_POST['barrio'] ?? '');

    // Validación básica
    if (empty($direccion) || empty($ciudad) || empty($barrio)) {
        header("Location: ../../views/cliente/perfil.php?error=1");
        exit;
    }

    try {
        $stmt = $conn->prepare("UPDATE usuarios SET direccion = ?, ciudad = ?, barrio = ? WHERE id_usuario = ?");
        if ($stmt->execute([$direccion, $ciudad, $barrio, $id_usuario])) {
            // Redirigir con mensaje de éxito
            header("Location: ../../views/cliente/perfil.php?exito=1");
            exit;
        } else {
            // Error en ejecución
            header("Location: ../../views/cliente/perfil.php?error=1");
            exit;
        }
    } catch (PDOException $e) {
        // Error de base de datos
        header("Location: ../../views/cliente/perfil.php?error=1");
        exit;
    }
} else {
    // Si acceden directamente al archivo sin POST
    header("Location: ../../views/cliente/perfil.php");
    exit;
}
