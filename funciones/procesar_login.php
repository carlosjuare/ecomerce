<?php
require_once __DIR__ . '/../core/db.php';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gmail = $_POST['gmail'] ?? '';
    $clave = $_POST['clave'] ?? '';

    // Validación básica
    if (empty($gmail) || empty($clave)) {
        header("Location: ../vistas/login.php?error=" . urlencode("Complete todos los campos."));
        exit;
    }

    $db = new Database();
    $conn = $db->connect();

    $sql = "SELECT id_usuario, nombre_usuario, clave FROM usuario WHERE gmail = :gmail LIMIT 1";
    $stmt = $conn->prepare($sql);
    $stmt->execute([':gmail' => $gmail]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        if (password_verify($clave, $usuario['clave'])) {
            // Inicio de sesión exitoso
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['nombre_usuario'] = $usuario['nombre_usuario'];

            header("Location: ../vistas/main.php");
            exit;
        } else {
            // Clave incorrecta
            header("Location: ../vistas/login.php?error=" . urlencode("La clave es incorrecta."));
            exit;
        }
    } else {
        // Usuario no encontrado
        header("Location: ../vistas/login.php?error=" . urlencode("El usuario no fue encontrado."));
        exit;
    }
} else {
    // Si no es POST, redirigir al formulario
    header("Location: ../vistas/login.php");
    exit;
}
