<?php
session_start();

// Verificar si el usuario está logueado
if (!isset($_SESSION['id_usuario'])) {
    echo "Usuario no logueado.";
    exit;
}

include('../db/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_publicacion']) && isset($_POST['id_usuario']) && isset($_POST['accion'])) {
    $idPublicacion = $_POST['id_publicacion'];
    $idUsuario = $_POST['id_usuario'];
    $accion = $_POST['accion'];

    if ($accion === 'agregar') {
        // Verificar si ya existe en favoritos
        $stmt_check = $DB_conn->prepare("SELECT id_publicacion FROM favoritos WHERE id_usuario = ? AND id_publicacion = ?");
        $stmt_check->bind_param("ii", $idUsuario, $idPublicacion);
        $stmt_check->execute();
        $stmt_check->store_result();

        if ($stmt_check->num_rows > 0) {
            echo "La publicación ya está en tus favoritos.";
        } else {
            // Insertar en la tabla de favoritos
            $query_insert = "INSERT INTO favoritos (id_usuario, id_publicacion) VALUES (?, ?)";
            $stmt_insert = $DB_conn->prepare($query_insert);
            $stmt_insert->bind_param("ii", $idUsuario, $idPublicacion);

            if ($stmt_insert->execute()) {
                echo "Publicación agregada a favoritos.";
            } else {
                echo "Error al agregar la publicación a favoritos: " . $stmt_insert->error;
            }
            $stmt_insert->close();
        }
        $stmt_check->close();

    } elseif ($accion === 'eliminar') {
        // Eliminar de la tabla de favoritos
        $query_delete = "DELETE FROM favoritos WHERE id_usuario = ? AND id_publicacion = ?";
        $stmt_delete = $DB_conn->prepare($query_delete);
        $stmt_delete->bind_param("ii", $idUsuario, $idPublicacion);

        if ($stmt_delete->execute()) {
            echo "Publicación eliminada de tus favoritos.";
        } else {
            echo "Error al eliminar la publicación de favoritos: " . $stmt_delete->error;
        }
        $stmt_delete->close();

    } else {
        echo "Acción no válida.";
    }

    $DB_conn->close();

} else {
    echo "Solicitud inválida: Faltan parámetros.";
}
?>