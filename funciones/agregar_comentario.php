<?php
require_once __DIR__ . '/../core/db.php';
session_start();

if (!isset($_SESSION['id_usuario']) || $_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Acceso no autorizado.");
}

$id_usuario = $_SESSION['id_usuario'];
$id_publicacion = (int)$_POST['id_publicacion'];
$comentario = trim($_POST['comentario']);

if (!$comentario) {
    exit("El comentario no puede estar vacío.");
}

$db = new Database();
$conn = $db->connect();

$sql = "INSERT INTO comentarios (id_publicacion, id_usuario, comentario, fecha_comentario)
        VALUES (:id_publicacion, :id_usuario, :comentario, NOW())";
$stmt = $conn->prepare($sql);
$stmt->execute([
    ':id_publicacion' => $id_publicacion,
    ':id_usuario' => $id_usuario,
    ':comentario' => $comentario
]);

header("Location: ../vistas/detalle_publicacion.php?id=$id_publicacion");
exit;
