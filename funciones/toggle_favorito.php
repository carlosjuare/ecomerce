<?php
session_start();
require_once __DIR__ . '/../core/db.php';

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    die("Debes iniciar sesión.");
}

$id_usuario = $_SESSION['id_usuario'];

// Validar la publicación recibida por POST
if (!isset($_POST['id_publicacion']) || !is_numeric($_POST['id_publicacion'])) {
    die("ID de publicación inválido.");
}

$id_publicacion = (int) $_POST['id_publicacion'];

$db = new Database();
$conn = $db->connect();

// Verificar si ya existe en favoritos
$sqlCheck = "SELECT 1 FROM favoritos WHERE id_usuario = :id_usuario AND id_publicacion = :id_publicacion";
$stmt = $conn->prepare($sqlCheck);
$stmt->execute([
    ':id_usuario' => $id_usuario,
    ':id_publicacion' => $id_publicacion
]);

$existe = $stmt->fetchColumn();

if ($existe) {
    // Ya está en favoritos → eliminar
    $sqlDelete = "DELETE FROM favoritos WHERE id_usuario = :id_usuario AND id_publicacion = :id_publicacion";
    $stmt = $conn->prepare($sqlDelete);
    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':id_publicacion' => $id_publicacion
    ]);
} else {
    // No está → agregar
    $sqlInsert = "INSERT INTO favoritos (id_usuario, id_publicacion) VALUES (:id_usuario, :id_publicacion)";
    $stmt = $conn->prepare($sqlInsert);
    $stmt->execute([
        ':id_usuario' => $id_usuario,
        ':id_publicacion' => $id_publicacion
    ]);
}

// Redirigir de vuelta (a donde vino)
$referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header("Location: $referer");
exit;
