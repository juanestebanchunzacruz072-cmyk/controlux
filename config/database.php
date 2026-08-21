<?php
$host = "127.0.0.1";
$user = "root";
$password = "";
$bd = "controlux_bd";
$port = 3306;

try {
    $conn = new PDO("mysql:host=$host;dbname=$bd;port=$port;charset=utf8", $user, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Error de Conexion: " . $e->getMessage());
}

