<?php
require_once __DIR__ . '/../core/db.php';
session_start();

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    exit("Acceso denegado.");
}

// Verificar sesión y datos requeridos
if (!isset($_SESSION['id_usuario']) || !isset($_POST['id_publicacion'])) {
    exit("Error: Faltan datos necesarios.");
}

$id_usuario = $_SESSION['id_usuario'];
$id_publicacion = (int)$_POST['id_publicacion'];

$db = new Database();
$conn = $db->connect();

// Verificar que la publicación pertenezca al usuario
$sqlCheck = "SELECT id_usuario FROM publicaciones WHERE id_publicacion = :id";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->execute([':id' => $id_publicacion]);
$publicacion = $stmtCheck->fetch(PDO::FETCH_ASSOC);

if (!$publicacion || $publicacion['id_usuario'] != $id_usuario) {
    exit("No tienes permisos para eliminar esta publicación.");
}

// Cambiar el estado a 'eliminado'
$sqlUpdate = "UPDATE publicaciones SET estado_publicacion = 'eliminado' WHERE id_publicacion = :id";
$stmtUpdate = $conn->prepare($sqlUpdate);

if ($stmtUpdate->execute([':id' => $id_publicacion])) {
    header("Location: ../vistas/gestionar_publicaciones.php?mensaje=eliminado");
    exit;
} else {
    exit("❌ Error al eliminar la publicación.");
}
