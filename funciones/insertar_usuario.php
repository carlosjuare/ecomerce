<?php
require_once __DIR__ . '/../core/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre_usuario']);
    $apellido = trim($_POST['apellido_usuario']);
    $gmail = trim($_POST['gmail']);
    $telefono = trim($_POST['telefono']);
    $clave = trim($_POST['clave']);
    $tipo_usuario = 1; // fijo

    // Validación simple
    if (!$nombre || !$apellido || !$gmail || !$telefono || !$clave) {
        exit("Faltan datos.");
    }

    // Encriptar la clave
    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

    $db = new Database();
    $conn = $db->connect();

    $sql = "INSERT INTO usuario (nombre_usuario, apellido_usuario, gmail, telefono, clave, id_tipo_usuario)
            VALUES (:nombre, :apellido, :gmail, :telefono, :clave, :tipo)";
    $stmt = $conn->prepare($sql);

    try {
        $stmt->execute([
            ':nombre'   => $nombre,
            ':apellido'=> $apellido,
            ':gmail'    => $gmail,
            ':telefono' => $telefono,
            ':clave'    => $clave_hash,
            ':tipo'     => $tipo_usuario
        ]);
         header("Location: ../vistas/login.php?mensaje=registrado");
        exit;
    } catch (PDOException $e) {
         echo "Error al registrar usuario: " . $e->getMessage();
    }
} else {
    exit("Acceso no permitido.");
}
