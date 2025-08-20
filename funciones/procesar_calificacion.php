<?php
session_start();
require_once __DIR__ . '/../core/db.php';

// Verificar que el usuario esté logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$id_calificador = $_SESSION['id_usuario'];

// Validar campos POST
if (!isset($_POST['id_calificado'], $_POST['puntuacion'])) {
    die("Datos incompletos.");
}

$id_calificado = (int) $_POST['id_calificado'];
$puntuacion = (int) $_POST['puntuacion'];
$comentario = trim($_POST['comentario'] ?? '');

// Evitar que el usuario se califique a sí mismo
if ($id_calificador === $id_calificado) {
    die("No puedes calificarte a ti mismo.");
}

// Validar rango de puntuación
if ($puntuacion < 1 || $puntuacion > 5) {
    die("Puntuación inválida.");
}

$db = new Database();
$conn = $db->connect();

// Verificar si ya calificó antes
$stmtCheck = $conn->prepare("SELECT * FROM calificaciones WHERE id_usuario_calificador = :calificador AND id_usuario_calificado = :calificado");
$stmtCheck->execute([
    ':calificador' => $id_calificador,
    ':calificado' => $id_calificado
]);

if ($stmtCheck->fetch()) {
    die("Ya has calificado a este usuario.");
}

// Insertar calificación
$stmtInsert = $conn->prepare("INSERT INTO calificaciones (id_usuario_calificador, id_usuario_calificado, puntuacion, comentario, fecha_calificacion) VALUES (:calificador, :calificado, :puntuacion, :comentario, NOW())");

$stmtInsert->execute([
    ':calificador' => $id_calificador,
    ':calificado' => $id_calificado,
    ':puntuacion' => $puntuacion,
    ':comentario' => $comentario
]);

// Redirigir a la página de origen o a un lugar por defecto
$redir = $_POST['redir'] ?? '';
if ($redir) {
    // Seguridad: validar que $redir sea una URL relativa o válida para evitar redirecciones abiertas
    if (strpos($redir, '/') === 0 || strpos($redir, 'detalle_publicacion.php') !== false) {
        header("Location: $redir");
        exit;
    }
}

// Si no viene redir, redirigir a la lista de publicaciones o homepage
header("Location: ../publicaciones/listado_publicaciones.php");
exit;
